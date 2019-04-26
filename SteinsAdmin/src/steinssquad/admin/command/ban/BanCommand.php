<?php
/**
 *
 *    _     _           _    _
 *   | \   / |         | |  | |
 *   |  \_/  | ___  ___| |__| |
 *   |       |/ _ \/ __| ___| |
 *   | |\_/| |  __/\__ \ |_ | |
 *   |_|   |_|\___||___/___| \_\
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author Mestl <mestl.dev@gmail.com>
 * @link   https://vk.com/themestl
 */

namespace steinssquad\admin\command\ban;


use pocketmine\command\CommandSender;
use pocketmine\IPlayer;
use steinssquad\admin\SteinsAdmin;
use steinssquad\perms\SteinsPerms;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\steinscore\utils\ParseUtils;


class BanCommand extends CustomCommand {

  private $banCooldown = [];

  public function __construct() {
    parent::__construct('ban', 'admin', 'steinscore.admin.ban.use');
    $this->registerOverload(['name' => 'player', 'type' => 'player'], ['name' => 'reason', 'type' => 'message']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) < 2) return self::RESULT_USAGE;


    $player = SteinsPlayer::getOfflinePlayer(array_shift($args));
    if (!($player instanceof IPlayer)) return self::RESULT_PLAYER_NOT_FOUND;

    if (SteinsAdmin::$instance->isBanned($player)) {
      $sender->sendMessage($this->translate('admin.ban-failed'));
      return self::RESULT_SUCCESS;
    }
    if (!($sender->hasPermission('steinscore.admin.ban'))) {
      if (isset($this->banCooldown[strtolower($sender->getName())]) && $this->banCooldown[strtolower($sender->getName())] > time()) {
        $sender->sendMessage($this->translate('generic.cooldown', ['time' => ParseUtils::placeholderFromTimestamp(
          $this->banCooldown[strtolower($sender->getName())] - time(), $sender
        )]));
        return self::RESULT_SUCCESS;
      }
      $this->banCooldown[strtolower($sender->getName())] = time() + 900;
    }
    if ($sender instanceof SteinsPlayer && SteinsPerms::$instance->getGroup($player)->getPriority() > $sender->getGroup()->getPriority())
      return self::RESULT_PLAYER_HAS_IMMUNITY;

    $reason = implode(" ", $args);
    $timestamp = 7 * 24 * 60 * 60;
    //TODO: вычислять время, основывая на причине

    SteinsAdmin::$instance->banPlayer($player, $reason, $sender instanceof SteinsPlayer? $sender->getCurrentName(): $sender->getName());

    $this->broadcast('admin.ban-success', ['player' => $sender instanceof SteinsPlayer? $sender->getCurrentName(): $sender->getName(), 'target' => SteinsPlayer::getPlayerName($player), 'reason' => $reason]);

    if ($player instanceof SteinsPlayer) $player->kick($player->localize('admin.ban-you-banned', ['admin' => $sender instanceof SteinsPlayer? $sender->getCurrentName(): $sender->getName(), 'reason' => $reason, 'time' => ParseUtils::placeholderFromTimestamp($timestamp, $player)]));
    return self::RESULT_SUCCESS;
  }
}
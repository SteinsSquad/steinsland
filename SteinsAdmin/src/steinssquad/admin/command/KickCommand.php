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

namespace steinssquad\admin\command;


use pocketmine\command\CommandSender;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\steinscore\utils\ParseUtils;


class KickCommand extends CustomCommand {

  private $kickCooldown = [];

  public function __construct() {
    parent::__construct('kick', 'admin', 'steinscore.admin.kick.use');
    $this->registerOverload(['name' => 'player', 'type' => 'player'], ['name' => 'reason', 'type' => 'message']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) < 2) return self::RESULT_USAGE;
    $player = SteinsPlayer::getPlayerByName(array_shift($args));
    if (!($player instanceof SteinsPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
    if (!($sender->hasPermission('steinscore.admin.kick'))) {
      if (isset($this->kickCooldown[strtolower($sender->getName())]) && $this->kickCooldown[strtolower($sender->getName())] > time()) {
        $sender->sendMessage($this->translate('generic.cooldown', [
          'time' => ParseUtils::placeholderFromTimestamp($this->kickCooldown[strtolower($sender->getName())] - time(), $sender)
        ]));
        return self::RESULT_SUCCESS;
      }
      $this->kickCooldown[strtolower($sender->getName())] = time() + 300;
    }
    $reason = implode(" ", $args);
    if ($sender instanceof SteinsPlayer && $player->getGroup()->getPriority() > $sender->getGroup()->getPriority()) return self::RESULT_PLAYER_HAS_IMMUNITY;
    $player->kick($player->localize('admin.kick-you-kicked', ['player' => $sender instanceof SteinsPlayer? $sender->getCurrentName(): $sender->getName(), 'reason' => $reason]), false);
    $this->broadcast('admin.kick-success', ['player' => $sender instanceof SteinsPlayer? $sender->getCurrentName(): $sender->getName(), 'target' => $player->getCurrentName(), 'reason' => $reason]);
    return self::RESULT_SUCCESS;
  }
}
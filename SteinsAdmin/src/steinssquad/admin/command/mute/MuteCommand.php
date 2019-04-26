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

namespace steinssquad\admin\command\mute;


use pocketmine\command\CommandSender;
use pocketmine\Server;
use steinssquad\admin\SteinsAdmin;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class MuteCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('mute', 'admin', 'steinscore.admin.mute');
    $this->registerOverload(['name' => 'player', 'type' => 'player'], ['name' => 'reason', 'type' => 'message']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) < 2) return self::RESULT_SUCCESS;
    $player = SteinsPlayer::getPlayerByName(array_shift($args));
    if (!($player instanceof SteinsPlayer) || $player === $sender) return self::RESULT_PLAYER_NOT_FOUND;
    if ($sender instanceof SteinsPlayer && $player->getGroup()->getPriority() > $sender->getGroup()->getPriority()) return self::RESULT_PLAYER_HAS_IMMUNITY;
    if (SteinsAdmin::$instance->mutePlayer($player, $sender instanceof SteinsPlayer ? $sender : null)) $this->broadcast('admin.mute-success', ['player' => $sender instanceof SteinsPlayer? $sender->getCurrentName(): $sender->getName(), 'target' => SteinsPlayer::getPlayerName($player), 'reason' => $reason = implode(" ", $args)]);
    else $sender->sendMessage($this->translate('admin.mute-failed'));
    return self::RESULT_SUCCESS;
  }
}
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

namespace steinssquad\teleport\command;


use pocketmine\command\CommandSender;
use pocketmine\IPlayer;
use pocketmine\level\Position;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\teleport\SteinsTeleport;


class BackCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('back', 'teleport', 'steinscore.teleport.back.use');

    $this->registerPermissibleOverload(['steinscore.teleport', 'steinscore.teleport.back'], ['name' => 'player', 'type' => 'player']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    $player = $sender;
    if (count($args) > 0) {
      if (!$sender->hasPermission('steinscore.teleport.back')) return self::RESULT_NO_RIGHTS;
      $player = SteinsPlayer::getOfflinePlayer(array_shift($args));
      if (!($player instanceof IPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
    }
    if (SteinsTeleport::$instance->getBackPosition($player) === null) {
      $sender->sendLocalizedMessage('teleport.back-failed');
      return self::RESULT_SUCCESS;
    }
    $sender->addTask(function(SteinsPlayer $player, Position $target) {
      $player->sendLocalizedPopup('teleport.teleporting');
      $player->teleport($target);
    }, $seconds = $sender->getTeleportCooldown(), SteinsTeleport::$instance->getBackPosition($player));
    $sender->sendLocalizedMessage(
      $sender === $player ?
        'teleport.back-success' . ($seconds > 0 ? '-wait' : '') :
        'teleport.back-success-player', ['seconds' => $seconds, 'player' => SteinsPlayer::getPlayerName($player)]);
    return self::RESULT_SUCCESS;
  }
}
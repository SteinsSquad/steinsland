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
use pocketmine\Server;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class SpawnCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('spawn', 'teleport', 'steinscore.teleport.spawn');
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    $sender->addTask(function(SteinsPlayer $player) {
      $player->sendLocalizedPopup('teleport.teleporting');
      $player->teleport(Server::getInstance()->getDefaultLevel()->getSpawnLocation());
    }, $seconds = $sender->getTeleportCooldown());
    $sender->sendLocalizedMessage($seconds > 0 ? 'teleport.spawn-success-wait' : 'teleport.spawn-success', ['seconds' => $seconds]);
    return self::RESULT_SUCCESS;
  }
}
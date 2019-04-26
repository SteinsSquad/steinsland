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
use pocketmine\level\Position;
use pocketmine\Server;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class RandomTeleportCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('randomtp', 'teleport', 'steinscore.teleport.randomtp', ['rtp']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    $players = Server::getInstance()->getOnlinePlayers();
    /** @var SteinsPlayer $player */
    $player = $players[array_rand($players)];
    $sender->addTask(function(SteinsPlayer $player, Position $target) {
      $player->sendLocalizedPopup('teleport.teleporting');
      $player->teleport($target);
    }, $seconds = $sender->getTeleportCooldown(), $player);
    $sender->sendLocalizedMessage($seconds > 0 ? 'teleport.randomtp-success-wait' : 'teleport.randomtp-success', ['seconds' => $seconds, 'player' => $player->getCurrentName()]);
    return self::RESULT_SUCCESS;
  }

}
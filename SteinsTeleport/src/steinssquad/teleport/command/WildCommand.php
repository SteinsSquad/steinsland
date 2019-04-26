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
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class WildCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('wild', 'teleport', 'steinscore.teleport.wild');
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;

    $position = $sender->asPosition();
    $position->x += mt_rand(-500, 500);
    $position->z += mt_rand(-500, 500);
    $position->y = $position->getLevel()->getHighestBlockAt($position->x, $position->z);

    $position->getLevel()->loadChunk($position->x >> 4, $position->y >> 4, true);

    $sender->addTask(function(SteinsPlayer $player, Position $target) {
      $player->sendLocalizedPopup('teleport.teleporting');
      $player->teleport($target);
    }, $seconds = $sender->getTeleportCooldown(), $position);
    $sender->sendLocalizedMessage($seconds > 0 ? 'teleport.wild-success-wait' : 'teleport.wild-success', ['seconds' => $seconds]);
    return self::RESULT_SUCCESS;
  }
}
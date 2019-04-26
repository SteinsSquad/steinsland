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
use pocketmine\level\Location;
use pocketmine\math\Math;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class TopCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('top', 'teleport', 'steinscore.feature.top', ['jump']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;

    $highestBlock = $sender->getLevel()->getHighestBlockAt(Math::floorFloat($sender->getX()), Math::floorFloat($sender->getZ())) + 1;
    if ($sender->getFloorY() === $highestBlock) {
      $sender->sendLocalizedMessage('teleport.top-failed');
      return self::RESULT_SUCCESS;
    }
    $location = $sender->asLocation();
    $location->y = $highestBlock;
    $sender->addTask(function(SteinsPlayer $player, Location $target) {
      $player->sendLocalizedPopup('teleport.teleporting');
      $player->teleport($target);
    }, $seconds = $sender->getTeleportCooldown(), $location);
    $sender->sendLocalizedMessage($seconds > 0 ? 'teleport.top-success-wait' : 'teleport.top-success', ['seconds' => $seconds]);
    return self::RESULT_SUCCESS;
  }
}
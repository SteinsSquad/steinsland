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

namespace steinssquad\feature\command\troll;


use pocketmine\command\CommandSender;
use pocketmine\entity\projectile\Arrow;
use pocketmine\math\Vector3;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class HailCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('hail', 'feature', 'steinscore.feature.hail');
    $this->registerOverload(['name' => 'player', 'type' => 'player']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) === 0) return self::RESULT_USAGE;
    $player = SteinsPlayer::getPlayerByName(array_shift($args));
    if (!($player instanceof SteinsPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
    if ($sender instanceof SteinsPlayer && $player->getGroup()->getPriority() > $sender->getGroup()->getPriority()) return self::RESULT_PLAYER_HAS_IMMUNITY;
    for($x = $player->getX() - 2; $x <= $player->getX() + 2; $x += 1){
      for($z = $player->getZ() - 2; $z <= $player->getZ() + 2; $z += 1){
        $pitch = -atan2($player->y - ($y = $player->getY() + 25), sqrt(($player->x - $x) ** 2 + ($player->z - $z) ** 2)) / M_PI * 180; //negative is up, positive is down
        $yaw = atan2($player->z - $z, $player->x - $x) / M_PI * 180 - 90;
        if($yaw < 0) $yaw += 360.0;
        $arrow = new Arrow($player->getLevel(), Arrow::createBaseNBT(
          new Vector3($x, $y, $z),
          new Vector3(
            -sin($yaw / 180 * M_PI) * cos($pitch / 180 * M_PI),
            -sin($pitch / 180 * M_PI),
            cos($yaw / 180 * M_PI) * cos($pitch / 180 * M_PI)
          ),
          $yaw, $pitch
        ), $sender instanceof SteinsPlayer? $sender : null);
        $arrow->setMotion($arrow->getMotion()->multiply(3.5));
        $arrow->spawnTo($player);
      }
    }
    $sender->sendMessage($this->translate('feature.hail-success', ['player' => $player->getCurrentName()]));
    return self::RESULT_SUCCESS;
  }
}
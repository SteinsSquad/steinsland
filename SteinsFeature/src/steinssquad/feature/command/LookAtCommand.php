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

namespace steinssquad\feature\command;


use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class LookAtCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('lookat', 'feature', 'steinscore.feature.lookat');
    $this->registerOverload(['name' => 'player', 'type' => 'player']);
    $this->registerOverload(['name' => 'position', 'type' => 'position']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (count($args) === 0) return self::RESULT_USAGE;
    if (count($args) < 3) {
      $target = SteinsPlayer::getPlayerExact(array_shift($args));
      if (!($target instanceof SteinsPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
      if ($target->isIgnorePlayer($sender)) return self::RESULT_PLAYER_IGNORES_YOU;
    } else {
      $x = $this->getRelativeDouble($sender->x, array_shift($args));
      $y = $this->getRelativeDouble($sender->y, array_shift($args), 0, 256);
      $z = $this->getRelativeDouble($sender->z, array_shift($args));
      $target = new Vector3($x, $y, $z);
    }
    $pk = new MovePlayerPacket();
    $pk->entityRuntimeId = $sender->getId();

    $pk->position = $sender;
    $yaw = atan2($target->z - $sender->z, $target->x - $sender->x) / M_PI * 180 - 90;
    if ($yaw < 0) $yaw += 360.0;
    $pk->yaw = $pk->headYaw = $yaw;
    $pk->pitch = -atan2($target->y - $sender->y, sqrt(($target->x - $sender->x) ** 2 + ($target->z - $sender->z) ** 2)) / M_PI * 180;

    $sender->sendLocalizedMessage($target instanceof SteinsPlayer ? 'feature.lookat-success-player' : 'feature.lookat-success-position', [
      'player' => $target instanceof SteinsPlayer ? $target->getCurrentName() : null,
      'x' => $target->x, 'y' => $target->y, 'z' => $target->z
    ]);
    return self::RESULT_SUCCESS;
  }
}
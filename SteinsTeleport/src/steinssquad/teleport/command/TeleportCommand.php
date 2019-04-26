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
use pocketmine\math\Vector3;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class TeleportCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('tp', 'teleport', 'steinscore.teleport.tp.use');

    $this->registerOverload(['name' => 'target', 'type' => 'player']);
    $this->registerOverload(['name' => 'position', 'type' => 'position']);
    $this->registerPermissibleOverload(
      ['steinscore.teleport', 'steinscore.teleport.tp'],
      ['name' => 'player', 'type' => 'player'],
      ['name' => 'target', 'type' => 'player']
    );
    $this->registerPermissibleOverload(
      ['steinscore.teleport', 'steinscore.teleport.tp'],
      ['name' => 'player', 'type' => 'player'],
      ['name' => 'position', 'type' => 'position']
    );
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) < 1 || count($args) > 6) return self::RESULT_USAGE;
    $target = null;
    $origin = $sender;
    if (count($args) === 1 || count($args) === 3) {
      if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;

      $target = $sender;
      if (count($args) === 1) {
        $target = SteinsPlayer::getPlayerByName(array_shift($args));
        if (!($target instanceof SteinsPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
      }
    } else {
      $target = SteinsPlayer::getPlayerByName(array_shift($args));
      if (!($target instanceof SteinsPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
      if (count($args) === 1) {
        $origin = $target;
        $target = SteinsPlayer::getPlayerByName(array_shift($args));
        if (!($target instanceof SteinsPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
      }
    }
    if (count($args) === 0) {
      $origin->teleport($target);
      $sender->sendMessage($this->module('tp-success', ['origin' => $origin->getCurrentName(), 'target' => $target->getCurrentName()]));
      return self::RESULT_SUCCESS;
    } elseif ($target->isValid() && (count($args) === 3 || count($args) === 5)) {
      $x = $this->getRelativeDouble($target->x, array_shift($args));
      $y = $this->getRelativeDouble($target->y, array_shift($args), 0, 256);
      $z = $this->getRelativeDouble($target->z, array_shift($args));
      $yaw = $target->getYaw();
      $pitch = $target->getPitch();
      if (count($args) === 6 or (count($args) === 2)) {
        $yaw = (float)array_shift($args);
        $pitch = (float)array_shift($args);
      }
      $target->teleport(new Vector3($x, $y, $z), $yaw, $pitch);
      $sender->sendMessage($this->module('tp-success-coordinates', ['origin' => $target->getCurrentName(), 'x' => round($x, 2), 'y' => round($y, 2), 'z' => round($z, 2)]));
      return self::RESULT_SUCCESS;
    }
    return self::RESULT_USAGE;
  }


}
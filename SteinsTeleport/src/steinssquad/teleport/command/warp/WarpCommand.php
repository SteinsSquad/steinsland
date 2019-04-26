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

namespace steinssquad\teleport\command\warp;


use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\teleport\SteinsTeleport;


class WarpCommand extends CustomCommand {



  public function __construct() {
    parent::__construct('warp', 'teleport','steinscore.teleport.warp');

    $this->registerOverload(['name' => 'warp', 'type' => 'string', 'optional' => true]);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) === 0) {
      $warps = SteinsTeleport::$instance->getWarps();
      $sender->sendMessage($this->module('warp-list-header', ['count' => count($warps)]));
      foreach ($warps as $warp => $warpData) {
        $sender->sendMessage($this->module('warp-list-line', ['warp' => $warp, 'owner' => $warpData['owner'] ?? 'server']));
      }
      return self::RESULT_SUCCESS;
    }
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (SteinsTeleport::$instance->warpExists($warp = array_shift($args))) {
      $sender->addTask(function(SteinsPlayer $player, Position $target) {
        $player->sendLocalizedPopup('teleport.teleporting');
        $player->teleport($target);
      }, $seconds = $sender->getTeleportCooldown(), SteinsTeleport::$instance->getWarpPosition($warp));
      $sender->sendLocalizedMessage($seconds > 0 ? 'teleport.warp-success-wait' : 'teleport.warp-success', ['seconds' => $seconds, 'warp' => $warp]);
    }
    $sender->sendLocalizedMessage('teleport.warp-failed');
    return self::RESULT_SUCCESS;
  }
}
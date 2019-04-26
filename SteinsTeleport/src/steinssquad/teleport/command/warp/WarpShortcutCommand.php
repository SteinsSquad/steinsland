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


class WarpShortcutCommand extends CustomCommand {

  private $warpName;
  private $position;

  public function __construct(string $warpName, Position $position = null) {
    parent::__construct($warpName, 'teleport', 'steinscore.teleport.warp');
    $this->setDescription('teleport.warp-description');
    $this->warpName = $warpName;
    $this->position = $position;
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    $sender->addTask(function(SteinsPlayer $player, Position $target) {
      $player->sendLocalizedPopup('teleport.teleporting');
      $player->teleport($target);
    }, $seconds = $sender->getTeleportCooldown(), $this->position ?? SteinsTeleport::$instance->getWarpPosition($this->warpName));
    $sender->sendLocalizedMessage($seconds > 0 ? 'teleport.warp-success-wait' : 'teleport.warp-success', ['seconds' => $seconds, 'warp' => $this->warpName]);
    return self::RESULT_SUCCESS;
  }
}
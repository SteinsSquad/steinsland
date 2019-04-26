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
use steinssquad\perms\SteinsPerms;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\teleport\SteinsTeleport;


class SetWarpCommand  extends CustomCommand {

  public function __construct() {
    parent::__construct('setwarp', 'teleport', 'steinscore.teleport.setwarp.use');
    $this->registerOverload(['name' => 'warp', 'type' => 'string']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (count($args) === 0) return self::RESULT_USAGE;
    $warpName = array_shift($args);
    if (isset(SteinsTeleport::$instance->getConfig()->get('warps')[strtolower($warpName)])) return self::RESULT_NO_RIGHTS;
    if (SteinsTeleport::$instance->warpExists($warpName) && !($sender->hasPermission('steinscore.teleport.setwarp'))) {
      $owner = SteinsTeleport::$instance->getWarp($warpName)['owner'];
      if ($owner === null || SteinsPerms::$instance->getGroup($owner)->getPriority() > $sender->getGroup()->getPriority())
        return self::RESULT_NO_RIGHTS;
    } else if (!($sender->hasPermission('steinscore.teleport.warps'))) {
      $possibleWarps = 5;
      if ($sender->hasPermission('steinscore.teleport.warps.3')) $possibleWarps = 3;
      else if ($sender->hasPermission('steinscore.teleport.warps.1')) $possibleWarps = 1;
      if (SteinsTeleport::$instance->getWarpCount($sender) >= $possibleWarps) {
        $sender->sendLocalizedMessage('teleport.setwarp-too-many-warps', ['warps' => $possibleWarps]);
        return self::RESULT_SUCCESS;
      }
    }
    $message = SteinsTeleport::$instance->warpExists($warpName) ? 'teleport.setwarp-success-update' : 'teleport.setwarp-success-set';
    SteinsTeleport::$instance->setWarp(
      $warpName, $sender->asPosition(), $sender->hasPermission('steinscore.teleport.setwarp'), $sender->hasPermission('steinscore.teleport.warps') ? null : $sender
    );
    $sender->sendLocalizedMessage($message, ['warp' => $warpName]);
    return self::RESULT_SUCCESS;
  }
}
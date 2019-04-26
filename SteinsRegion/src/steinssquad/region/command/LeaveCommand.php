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

namespace steinssquad\region\command;


use pocketmine\command\CommandSender;
use steinssquad\region\SteinsRegion;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class LeaveCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('leave', 'region', 'steinscore.region.claim');
    $this->registerOverload(['name' => 'region', 'type' => 'string']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (count($args) === 0) return self::RESULT_USAGE;
    if (!(SteinsRegion::$instance->regionExists($region = array_shift($args)))) {
      $sender->sendLocalizedMessage('region.generic-region-not-found');
      return self::RESULT_SUCCESS;
    }
    if (
      SteinsRegion::$instance->hasRegionPermission($region, $sender, SteinsRegion::PERMISSION_OWNER) ||
      SteinsRegion::$instance->hasRegionPermission($region, $sender, SteinsRegion::PERMISSION_NOT_MEMBER)
    ) {
      $sender->sendLocalizedMessage('region.leave-failed');
      return self::RESULT_SUCCESS;
    }
    SteinsRegion::$instance->removeRegionMember($region, $sender);
    $sender->sendLocalizedMessage('region.leave-success', ['region' => $region]);
    return self::RESULT_SUCCESS;
  }
}
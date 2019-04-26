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


class ClaimCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('claim', 'region', 'steinscore.region.claim');
    $this->registerOverload(['name' => 'region', 'type' => 'string']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (count($args) === 0) return self::RESULT_USAGE;
    $regionName = array_shift($args);
    if (SteinsRegion::$instance->regionExists($regionName)) {
      $sender->sendLocalizedMessage('region.claim-region-exists');
      return self::RESULT_SUCCESS;
    }
    if (count($selections = SteinsRegion::$instance->getSelections($sender)) !== 2) {
      $sender->sendLocalizedMessage('region.claim-no-selections');
      return self::RESULT_SUCCESS;
    }
    $collideRegions = [];
    foreach (SteinsRegion::$instance->getRegionsBetween(...$selections) as $region) {
      if (!(SteinsRegion::$instance->hasRegionPermission($region, $sender, SteinsRegion::PERMISSION_OWNER))) $collideRegions[] = $region;
    }
    if (count($collideRegions) > 0) {
      $sender->sendLocalizedMessage('region.claim-collides', ['regions' => implode("&f, &a", $collideRegions)]);
      return self::RESULT_SUCCESS;
    }
    if (count(SteinsRegion::$instance->getPlayerRegions($sender, SteinsRegion::PERMISSION_OWNER)) >= $sender->getGroup()->getRegionCount()) {
      $sender->sendLocalizedMessage('region.claim-too-many-regions', ['count' => $sender->getGroup()->getRegionCount()]);
      return self::RESULT_SUCCESS;
    }
    $square = abs(((max($selections[0]->getX(), $selections[1]->getX()) - min($selections[0]->getX(), $selections[1]->getX()) + 1) * (max($selections[0]->getZ(), $selections[1]->getZ()) - min($selections[0]->getZ(), $selections[1]->getZ()) + 1)));
    if ($square > $sender->getGroup()->getRegionSize()) {
      $sender->sendLocalizedMessage('region.claim-oversize', ['count' => $sender->getGroup()->getRegionSize()]);
      return self::RESULT_SUCCESS;
    }
    SteinsRegion::$instance->claimRegion($regionName, $selections[0], $selections[1], $sender);
    SteinsRegion::$instance->removeSelections($sender);
    $sender->sendLocalizedMessage('region.claim-success', ['region' => $regionName, 'size' => $square]);
    return self::RESULT_SUCCESS;
  }
}
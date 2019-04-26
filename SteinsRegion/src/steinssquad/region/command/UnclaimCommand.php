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


class UnclaimCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('unclaim', 'region', 'steinscore.region.claim');
    $this->registerOverload(['name' => 'region', 'type' => 'string']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) === 0) return self::RESULT_USAGE;
    if (!(SteinsRegion::$instance->regionExists($region = array_shift($args)))) {
      $sender->sendMessage($this->translate('region.generic-region-not-found'));
      return self::RESULT_SUCCESS;
    }
    if (
      $sender instanceof SteinsPlayer &&
      !(SteinsRegion::$instance->hasRegionPermission($region, $sender, SteinsRegion::PERMISSION_OWNER)) &&
      !($sender->hasPermission('steinscore.region'))
    ) return self::RESULT_NO_RIGHTS;

    $sender->sendMessage($this->module('unclaim-success', ['region' => $region->getName()]));
    SteinsRegion::$instance->unclaimRegion($region->getName());
    return self::RESULT_SUCCESS;
  }
}
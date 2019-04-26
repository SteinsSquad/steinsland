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


class InfoCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('info', 'region', 'steinscore.region.claim');
    $this->registerOverload(['name' => 'region', 'type' => 'string']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) === 0 && !($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (count($args) > 0) {
      if (!(SteinsRegion::$instance->regionExists($region = array_shift($args)))) {
        $sender->sendMessage($this->module('generic-region-not-found'));
        return self::RESULT_SUCCESS;
      }
      $regions = [$region];
    } else {
      if (count($regions = SteinsRegion::$instance->getRegionsInside($sender)) === 0) {
        $sender->sendMessage($this->module('info-empty'));
        return self::RESULT_SUCCESS;
      }
    }
    foreach ($regions as $region) {
      $members = [];
      $admins = [];
      $owner = null;
      foreach (SteinsRegion::$instance->getRegionMembers($region) as $member => $permission) {
        if ($permission === SteinsRegion::PERMISSION_USER) $members[] = $member;
        else if ($permission === SteinsRegion::PERMISSION_ADMIN) $admins[] = $member;
        else if ($permission === SteinsRegion::PERMISSION_OWNER) $owner = $member;
      }
      $sender->sendMessage($this->module('info-header', ['region' => $region, 'owner' => $owner]));
      $sender->sendMessage($this->module('info-position', [
        'min' => implode("&f, &6", SteinsRegion::$instance->getRegionMin($region)),
        'max' => implode("&f, &6", SteinsRegion::$instance->getRegionMax($region)),
        'size' => abs((
          (SteinsRegion::$instance->getRegionMax($region)[0] - SteinsRegion::$instance->getRegionMin($region)[0] + 1) *
          (SteinsRegion::$instance->getRegionMax($region)[1] - SteinsRegion::$instance->getRegionMin($region)[1] + 1)
        ))
      ]));

      $sender->sendMessage($this->module('info-members', ['members' => implode("&f, &a", $members), 'admins' => implode("&f, &c", $admins)]));
      $flags = [];
      foreach (SteinsRegion::$instance->getRegionFlags($region) as $flag => $val) {
        if (is_bool($val)) $flags[] = ($val ? "&a" : "&c") . $flag;
        else $flags[] = "&a$flag&d=&7$val";
      }
      $sender->sendMessage($this->module('info-flags', ['flags' => implode("&f, &a", $flags)]));
    }
    return self::RESULT_SUCCESS;
  }
}
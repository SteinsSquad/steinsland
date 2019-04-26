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


class FlagCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('flag', 'region','steinscore.region.claim', ['setflag']);
    $this->registerOverload(['name' => 'region', 'type' => 'string'], ['name' => 'flag', 'type' => 'rawtext', 'enum' => [
      'name' => 'flags',
      'values' => array_keys(SteinsRegion::FLAGS)
    ]], ['name' => 'value', 'type' => 'string', 'optional' => true]);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) < 2) return self::RESULT_USAGE;
    if (!(SteinsRegion::$instance->regionExists($region = array_shift($args)))) {
      $sender->sendMessage($this->module('generic-region-not-found'));
      return self::RESULT_SUCCESS;
    }
    if (
      $sender instanceof SteinsPlayer &&
      SteinsRegion::$instance->hasRegionPermission($region, $sender, SteinsRegion::PERMISSION_NOT_MEMBER) &&
      !($sender->hasPermission('steinscore.region'))
    ) return self::RESULT_NO_RIGHTS;
    if (!(isset(SteinsRegion::FLAGS[$flag = strtolower(array_shift($args))]))) {
      $sender->sendMessage($this->module('flag-not-found', ['flags' => implode("&f, &a", array_keys(SteinsRegion::FLAGS))]));
      return self::RESULT_SUCCESS;
    }
    if (count($args) === 0) {
      $sender->sendMessage($this->module('flag-success-value', ['region' => $region, 'flag' => $flag, 'value' => is_bool($value = SteinsRegion::$instance->getRegionFlag($region, $flag)) ? ($value ? 'allow' : 'deny') : $value]));
      return self::RESULT_SUCCESS;
    }
    if (!($sender->hasPermission("steinscore.region.flag-$flag"))) return self::RESULT_NO_RIGHTS;
    $value = implode(" ", $args);
    $old = SteinsRegion::$instance->getRegionFlag($region, $flag);
    if (is_bool(SteinsRegion::FLAGS[$flag])) {
      if (strtolower($value) === 'true' || strtolower($value) === 'allow' || strtolower($value) === 'yes') {
        $value = true;
      } else {
        $value = false;
      }
    } else if (is_int(SteinsRegion::FLAGS[$flag])) {
      $value = intval($value);
    } else if (is_float(SteinsRegion::FLAGS[$flag])) {
      $value = floatval($value);
    } else if (is_string(SteinsRegion::FLAGS[$flag])) {
      $value = $value === 'clear' ? '' : $value;
      if (mb_strlen($value) > 0) $value = "&f$value";
    }

    SteinsRegion::$instance->setRegionFlag($region, $flag, $value);
    $sender->sendMessage($this->module('flag-success-set', [
      'region' => $region,
      'flag' => $flag,
      'value' => is_bool($value) ? ($value ? 'allow' : 'deny') : $value,
      'old' => is_bool($old) ? ($old ? 'allow' : 'deny') : $old
    ]));
    return self::RESULT_SUCCESS;
  }
}
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
use pocketmine\IPlayer;
use steinssquad\region\SteinsRegion;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class AddMemberCommand extends CustomCommand {


  public function __construct() {
    parent::__construct('addmember', 'region', 'steinscore.region.claim');
    $this->registerOverload(['name' => 'region', 'type' => 'string'], ['name' => 'player', 'type' => 'player']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) < 2) return self::RESULT_USAGE;
    if (!(SteinsRegion::$instance->regionExists($region = array_shift($args)))) {
      $sender->sendMessage($this->module('generic-region-not-found'));
      return self::RESULT_SUCCESS;
    }
    if (
      $sender instanceof SteinsPlayer &&
      !(SteinsRegion::$instance->hasRegionPermission($region, $sender, SteinsRegion::PERMISSION_GT_USER)) &&
      !($sender->hasPermission('steinscore.region'))
    ) return self::RESULT_NO_RIGHTS;
    $player = SteinsPlayer::getOfflinePlayer(array_shift($args));
    if (!($player instanceof IPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
    if (SteinsRegion::$instance->addRegionMember($region, $player)) {
      $sender->sendMessage($this->module('addmember-success', ['player' => SteinsPlayer::getPlayerName($player), 'region' => $region]));
      if ($player instanceof SteinsPlayer) $player->sendLocalizedMessage('region.addmember-player', ['region' => $region]);
      return self::RESULT_SUCCESS;
    }
    $sender->sendMessage($this->translate('region.addmember-failed', ['player' => SteinsPlayer::getPlayerName($player)]));
    return self::RESULT_SUCCESS;
  }
}
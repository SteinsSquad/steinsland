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
use pocketmine\level\Position;
use pocketmine\Server;
use steinssquad\economy\SteinsEconomy;
use steinssquad\region\SteinsRegion;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class BuyRegion extends CustomCommand {

  public function __construct() {
    parent::__construct('buyrg', 'region', 'steinscore.region.claim');
  }

  public function onCommand(CommandSender $sender, array $args) {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    $regions = SteinsRegion::$instance->getRegionsInside($sender);
    if (is_null($region = array_shift($regions))) {
      $sender->sendLocalizedMessage('region.generic-region-not-found');
      return self::RESULT_SUCCESS;
    }
    if (($price = SteinsRegion::$instance->getRegionFlag($region, 'price')) <= 0) {
      $sender->sendLocalizedMessage('region.buyrg-failed', ['region' => $region]);
      return self::RESULT_SUCCESS;
    } else if (SteinsRegion::$instance->hasRegionPermission($region, $sender, SteinsRegion::PERMISSION_OWNER)) {
      $sender->sendLocalizedMessage('region.buyrg-epic-fail');
      return self::RESULT_SUCCESS;
    }

    $level = Server::getInstance()->getLevelByName(SteinsRegion::$instance->getRegionLevel($region));
    $min = (new Position(...SteinsRegion::$instance->getRegionMin($region)))->setLevel($level);
    $max = (new Position(...SteinsRegion::$instance->getRegionMax($region)))->setLevel($level);

    if (count($regions) > 0 || count($regions = SteinsRegion::$instance->getRegionsBetween($min, $max)) > 1) {
     $sender->sendLocalizedMessage('region.buyrg-collide');
     return self::RESULT_SUCCESS;
    } else if (!($sender->hasMoney($price))) return self::RESULT_NOT_ENOUGH_MONEY;

    $player = SteinsPlayer::getOfflinePlayerExact(array_search(SteinsRegion::PERMISSION_OWNER, SteinsRegion::$instance->getRegionMembers($region)));
    if (!($player instanceof IPlayer)) return self::RESULT_PLAYER_NOT_FOUND;

    SteinsEconomy::$instance->addMoney($player, $price);
    $sender->reduceMoney($price);

    SteinsRegion::$instance->removeRegionMember($region, $player);
    SteinsRegion::$instance->addRegionMember($region, $sender, SteinsRegion::PERMISSION_OWNER);

    SteinsRegion::$instance->setRegionFlag($region, 'price', -1);
    $sender->sendLocalizedMessage('region.buyrg-success', ['region' => $region, 'amount' => $price]);
    return self::RESULT_SUCCESS;
  }
}
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

namespace steinssquad\clan\command;


use pocketmine\command\CommandSender;
use steinssquad\clan\SteinsClan;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class ClanSetHomeCommand extends CustomCommand {

  private const CLAN_HOME_PRICE = 5000;

  public function __construct() {
    parent::__construct('csethome', 'clan', 'steinscore.clan.create');
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (is_null($clanName = SteinsClan::$instance->getPlayerClan($sender))) {
      $sender->sendLocalizedMessage('clan.clan-not-found');
      return self::RESULT_SUCCESS;
    }
    if (SteinsClan::$instance->getMoney($clanName) < self::CLAN_HOME_PRICE) self::RESULT_NOT_ENOUGH_MONEY;
    if (SteinsClan::$instance->getLevel($clanName) === 0) {
      $sender->sendLocalizedMessage('clan.clan-level-need', ['level' => 1]);
      return self::RESULT_SUCCESS;
    }
    if (!(SteinsClan::$instance->hasPlayerRole($sender, SteinsClan::ROLE_GT_USER))) return self::RESULT_NO_RIGHTS;
    SteinsClan::$instance->setHome($clanName, $sender->asPosition());
    $sender->sendLocalizedMessage('clan.csethome-success');
    return self::RESULT_SUCCESS;
  }
}
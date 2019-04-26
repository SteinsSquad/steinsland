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


class ClanCreateCommand extends CustomCommand {

  private const CLAN_PRICE = 15000;

  public function __construct() {
    parent::__construct('ccreate', 'clan', 'steinscore.clan.create');
    $this->registerOverload(['name' => 'clan', 'type' => 'string']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (count($args) === 0) return self::RESULT_USAGE;
    if (SteinsClan::$instance->getPlayerClan($sender) !== null) {
      $sender->sendLocalizedMessage('clan.ccreate-failed');
      return self::RESULT_SUCCESS;
    }
    if (SteinsClan::$instance->clanExists($clanName = array_shift($args))) {
      $sender->sendLocalizedMessage('clan.ccreate-exists');
      return self::RESULT_SUCCESS;
    }
    if (!(ctype_alnum($clanName)) || mb_strlen($clanName) > 12 || mb_strlen($clanName) < 3) return self::RESULT_USAGE;
    if (!($sender->hasMoney(self::CLAN_PRICE))) return self::RESULT_NOT_ENOUGH_MONEY;
    $sender->reduceMoney(self::CLAN_PRICE);
    SteinsClan::$instance->createClan($clanName, $sender);
    $sender->sendLocalizedMessage('clan.ccreate-success', ['clan' => $clanName]);
    return self::RESULT_SUCCESS;
  }
}
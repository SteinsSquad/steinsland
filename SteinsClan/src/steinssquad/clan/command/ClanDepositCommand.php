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


class ClanDepositCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('cdeposit', 'clan', 'steinscore.clan.create');
    $this->registerOverload(['name' => 'amount', 'type' => 'int']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (count($args) === 0) return self::RESULT_USAGE;
    if (!(is_numeric($amount = array_shift($args)))) return self::RESULT_USAGE;
    $amount = abs(intval($amount));
    if (($clanName = SteinsClan::$instance->getPlayerClan($sender)) === null) {
      $sender->sendLocalizedMessage('clan.clan-not-found');
      return self::RESULT_SUCCESS;
    }
    if (!($sender->hasMoney($amount))) return self::RESULT_NOT_ENOUGH_MONEY;
    $sender->reduceMoney($amount);
    SteinsClan::$instance->addMoney($clanName, $amount);
    if (mt_rand(0, 10) === 0 && $amount >= 1000) SteinsClan::$instance->addExp($clanName, intval($amount * 0.001));
    $sender->sendLocalizedMessage('clan.cdeposit-success', ['amount' => $amount]);
    return self::RESULT_SUCCESS;
  }
}
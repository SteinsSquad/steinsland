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

namespace steinssquad\economy\command;


use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\IPlayer;
use steinssquad\economy\SteinsEconomy;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class SetMoneyCommand extends CustomCommand {

  public function __construct() {
    parent::__construct("setmoney", 'economy', 'steinscore.economy.setmoney.use');
    $this->registerOverload(['name' => 'amount', 'type' => 'int']);
    $this->registerPermissibleOverload(
      [
        'steinscore.economy.setmoney'
      ], ['name' => 'player', 'type' => 'player'], ['name' => 'amount', 'type' => 'int']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) === 0) return self::RESULT_USAGE;
    $playerName = $sender->getName();
    $amount = array_shift($args);
    if (count($args) === 0 && $sender instanceof ConsoleCommandSender) return self::RESULT_IN_GAME;
    if (count($args) >= 1) {
      if (!($sender->hasPermission('steinscore.economy.setmoney'))) return self::RESULT_NO_RIGHTS;
      $playerName = $amount;
      $amount = array_shift($args);
      if (!(is_numeric($amount))) return self::RESULT_USAGE;
    }
    $amount = abs(intval($amount));
    $player = SteinsPlayer::getOfflinePlayer($playerName);
    if (!($player instanceof IPlayer)) return self::RESULT_PLAYER_NOT_FOUND;

    if (SteinsEconomy::$instance->setMoney($player, $amount)) {
      $sender->sendMessage($this->module('setmoney-success', ['player' => SteinsPlayer::getPlayerName($player), 'money' => $amount]));
      return self::RESULT_SUCCESS;
    }
    $sender->sendMessage($this->module('setmoney-failed'));
    return self::RESULT_SUCCESS;
  }
}
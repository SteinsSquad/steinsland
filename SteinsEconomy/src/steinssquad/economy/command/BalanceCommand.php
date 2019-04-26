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
use pocketmine\IPlayer;
use steinssquad\economy\SteinsEconomy;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class BalanceCommand extends CustomCommand {

  public function __construct() {
    parent::__construct("balance", 'economy', 'steinscore.economy.balance.use', ['money']);
    $this->registerPermissibleOverload(['steinscore.economy', 'steinscore.economy.balance'], ['name' => 'player', 'type' => 'player', 'optional' => true]);
  }


  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) === 0 && $sender instanceof SteinsPlayer) {
      $sender->sendLocalizedMessage('economy.balance-success', ['money' => $sender->getMoney()]);
      return self::RESULT_SUCCESS;
    }
    if (!($sender->hasPermission('steinscore.economy.balance'))) return self::RESULT_NO_RIGHTS;
    if (count($args) === 0) return self::RESULT_USAGE;
    $player = SteinsPlayer::getOfflinePlayer(array_shift($args));
    if (!($player instanceof IPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
    $sender->sendMessage($this->module('balance-player-success', [
      'money' => SteinsEconomy::$instance->getMoney($player), 'player' => SteinsPlayer::getPlayerName($player)
    ]));
    return self::RESULT_SUCCESS;
  }
}
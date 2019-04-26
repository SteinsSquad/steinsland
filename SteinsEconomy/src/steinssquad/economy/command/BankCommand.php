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
use steinssquad\economy\SteinsEconomy;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class BankCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('bank', 'economy', 'steinscore.economy.bank');
    $this->registerOverload(
      ['name' => 'action', 'type' => 'rawtext', 'enum' => ['values' => ['deposit', 'withdraw', 'balance'], 'name' => 'actions']],
      ['name' => 'amount', 'type' => 'int', 'optional' => true]
    );
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (count($args) === 0) return self::RESULT_USAGE;
    $action = strtolower(array_shift($args));
    if ($action !== 'withdraw' && $action !== 'deposit' && $action !== 'balance') return self::RESULT_USAGE;
    $amount = array_shift($args);
    if ($action !== 'balance' && (is_null($amount) || !(is_numeric($amount)))) return self::RESULT_USAGE;
    $amount = abs(intval($amount));
    if ($action === 'deposit') {
      if (!(SteinsEconomy::$instance->bankDeposit($sender, $amount))) return self::RESULT_NOT_ENOUGH_MONEY;
      $sender->sendLocalizedMessage('economy.bank-deposit-success', ['amount' => $amount]);
    } else if ($action === 'withdraw') {
      if (is_null(SteinsEconomy::$instance->bankBalance($sender))) {
        $sender->sendLocalizedMessage('economy.bank-balance-failed');
        return self::RESULT_SUCCESS;
      }
      if (!(SteinsEconomy::$instance->bankWithdraw($sender, $amount))) return self::RESULT_NOT_ENOUGH_MONEY;
      $sender->sendLocalizedMessage('economy.bank-withdraw-success', ['amount' => $amount]);
    } else {
      if (($bal = SteinsEconomy::$instance->bankBalance($sender)) === null) {
        $sender->sendLocalizedMessage('economy.bank-balance-failed');
        return self::RESULT_SUCCESS;
      }
      $sender->sendLocalizedMessage('economy.bank-balance-success', ['amount' => $bal]);
    }
    return self::RESULT_SUCCESS;
  }
}
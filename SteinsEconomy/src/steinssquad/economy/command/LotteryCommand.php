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
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class LotteryCommand extends CustomCommand {

  public const LOTTERY_PRICE = 1000;

  public function __construct() {
    parent::__construct('lottery', 'economy', 'steinscore.feature.lottery');
    $this->registerOverload(['name' => 'amount', 'type' => 'int', 'optional' => true]);
  }

  public function onCommand(CommandSender $sender, array $args) {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    $amount = abs(intval(array_shift($args) ?? self::LOTTERY_PRICE));
    if ($amount < self::LOTTERY_PRICE) return self::RESULT_USAGE;
    if (!($sender->hasMoney($amount))) return self::RESULT_NOT_ENOUGH_MONEY;
    $sender->reduceMoney($amount);

    if (mt_rand(0, 100) > 25) {
      $sender->sendLocalizedMessage('economy.lottery-failed');
      return self::RESULT_SUCCESS;
    }

    $combination = str_pad(strval(mt_rand(000, 999)), 3, '0');
    $score = str_pad(strval(mt_rand(000, 999)), 3, '0');
    $points = 0;
    if ($combination[0] === $score[0]) $points++;
    if ($combination[1] === $score[1]) $points++;
    if ($combination[2] === $score[2]) $points++;

    if ($combination === $score) {
      $sender->addMoney($amount * 2);
      $sender->sendLocalizedMessage('economy.lottery-triple', ['money' => $amount * 3]);
      return self::RESULT_SUCCESS;
    }

    if ($points === 0) {
      $sender->sendLocalizedMessage('economy.lottery-failed');
      return self::RESULT_SUCCESS;
    } else if ($points === 3) {
      $sender->addMoney($amount * 2);
      $sender->sendLocalizedMessage('economy.lottery-jackpot', ['money' => $amount * 2]);
      return self::RESULT_SUCCESS;
    }
    $sender->addMoney(round($amount * 2 / (4 - $points)));
    $sender->sendLocalizedMessage('economy.lottery-success', ['money' => round($amount * 2 / (4 - $points))]);

    return self::RESULT_SUCCESS;
  }
}
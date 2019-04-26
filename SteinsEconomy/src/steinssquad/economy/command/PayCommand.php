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


class PayCommand extends CustomCommand {

  public function __construct() {
    parent::__construct("pay", 'economy', 'steinscore.economy.pay');
    $this->registerOverload(['name' => 'player', 'type' => 'player'], ['name' => 'amount', 'type' => 'int']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (count($args) < 2) return self::RESULT_USAGE;

    $player = SteinsPlayer::getOfflinePlayer(array_shift($args));
    if (!($player instanceof IPlayer) || $player === $sender) return self::RESULT_PLAYER_NOT_FOUND;
    if ($player instanceof SteinsPlayer && $player->isIgnorePlayer($sender)) return self::RESULT_PLAYER_IGNORES_YOU;

    $amount = array_shift($args);
    if (!is_numeric($amount)) return self::RESULT_USAGE;
    $amount = abs(intval($amount));


    if (!($sender->hasMoney($amount))) return self::RESULT_NOT_ENOUGH_MONEY;
    if (!(SteinsEconomy::$instance->payMoney($sender, $player, $amount))) {
      $sender->sendMessage($this->translate('economy.pay-failed'));
      return self::RESULT_SUCCESS;
    }
    $sender->sendMessage($this->translate('economy.pay-success', ['money' => $amount, 'player' => SteinsPlayer::getPlayerName($player)]));
    if ($player instanceof SteinsPlayer) {
      $player->sendLocalizedMessage('economy.pay-got', ['money' => $amount, 'player' => $sender->getCurrentName()]);
    }
    return self::RESULT_SUCCESS;
  }
}
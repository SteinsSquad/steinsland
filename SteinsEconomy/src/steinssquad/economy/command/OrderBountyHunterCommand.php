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


class OrderBountyHunterCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('obh', 'economy', 'steinscore.economy.obh');
    $this->registerOverload(['name' => 'player', 'type' => 'player'], ['name' => 'amount', 'type' => 'int']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (count($args) < 2) return self::RESULT_USAGE;
    $player = SteinsPlayer::getPlayerByName(array_shift($args));
    if (!($player instanceof SteinsPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
    $amount = array_shift($args);
    if (!(is_numeric($amount))) return self::RESULT_USAGE;

    if (SteinsEconomy::$instance->orderPlayer($player, $sender, abs(intval($amount)))) {
      $sender->sendLocalizedMessage('economy.obh-success', [
        'player' => $sender->getCurrentName(),
        'target' => $player->getCurrentName(),
        'amount' => SteinsEconomy::$instance->getHeadPrice($player)
      ]);
      return self::RESULT_SUCCESS;
    }
    return self::RESULT_NOT_ENOUGH_MONEY;
  }
}
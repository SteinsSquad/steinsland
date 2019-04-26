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

namespace steinssquad\feature\command\helpful;


use pocketmine\command\CommandSender;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class ExpFlyCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('expfly', 'feature', 'steinscore.feature.expfly');
  }

  public function onCommand(CommandSender $sender, array $args) {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if ($sender->hasPermission('steinscore.feature.fly.use')) {
      $sender->sendLocalizedMessage('feature.expfly-failed');
      return self::RESULT_SUCCESS;
    }

    $flyCalcTask = function(SteinsPlayer $player, \Closure $flyCalcTask) {
      if ($player->isFlying()) {
        $expRequire = (int)($player->getInAirTicks() / 20);
        if ($player->getCurrentTotalXp() <= $expRequire) {
          $player->setFlying(false);
          $player->setAllowFlight(false);
          $player->subtractXp($player->getCurrentTotalXp());
          return false;
        }
        $player->subtractXp($expRequire);
      }
      $player->addTask($flyCalcTask, 1, $flyCalcTask);
      return true;
    };

    $sender->addTask($flyCalcTask, 1, $flyCalcTask);
    $sender->setAllowFlight(true);
    $sender->sendLocalizedMessage('feature.expfly-success');
    return self::RESULT_SUCCESS;
  }
}
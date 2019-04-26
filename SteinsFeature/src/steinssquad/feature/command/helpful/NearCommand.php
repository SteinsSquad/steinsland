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


class NearCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('near', 'feature', 'steinscore.feature.near');
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    $near = [];
    /** @var SteinsPlayer $player */
    foreach ($sender->getLevel()->getPlayers() as $player) {
      if ($player !== $sender && $player->distance($sender) < 100) $near[$player->getCurrentName()] = ceil($player->distance($sender));
    }
    if (count($near) > 0) {
      $sender->sendLocalizedMessage('feature.near-header', ['count' => count($near)]);
      foreach ($near as $player => $distance) $sender->sendLocalizedMessage('feature.near-line', ['player' => $player, 'distance' => $distance]);
      return self::RESULT_SUCCESS;
    }
    $sender->sendLocalizedMessage('feature.near-failed');
    return self::RESULT_SUCCESS;
  }
}
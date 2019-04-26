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

namespace steinssquad\feature\command;


use pocketmine\command\CommandSender;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class HackCommand extends CustomCommand {

  private $attempts = [];

  public function __construct() {
    parent::__construct('hack', 'feature', 'steinscore.feature.hack');
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (isset($this->attempts[$sender->getLowerCaseName()])) {
      $sender->sendLocalizedMessage('feature.hack-later');
      return self::RESULT_SUCCESS;
    }
    $rand = mt_rand(0, 9999);
    $need = mt_rand(0, 9999);
    if ($rand === $need) $rand -= $rand === 0 ? -1 : 1;

    $sender->sendLocalizedMessage('feature.hack-failed', ['rand' => $rand, 'need' => $need]);
    $this->attempts[$sender->getLowerCaseName()] = true;
    return self::RESULT_SUCCESS;
  }
}
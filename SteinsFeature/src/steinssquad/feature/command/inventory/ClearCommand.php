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

namespace steinssquad\feature\command\inventory;


use pocketmine\command\CommandSender;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class ClearCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('clear', 'feature', 'steinscore.feature.clear', ['ci']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if ($sender->isCreative()) return self::RESULT_NOT_FOR_CREATIVE;
    if (count($sender->getInventory()->getContents()) <= 0) {
      $sender->sendLocalizedMessage('feature.clear-failed');
      return self::RESULT_SUCCESS;
    }
    $sender->sendLocalizedMessage('feature.clear-success');
    $sender->getInventory()->clearAll();
    $sender->getArmorInventory()->clearAll();
    return self::RESULT_SUCCESS;
  }
}
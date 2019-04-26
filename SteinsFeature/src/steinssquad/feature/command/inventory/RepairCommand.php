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
use pocketmine\item\Durable;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class RepairCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('repair', 'feature', 'steinscore.feature.repair', ['fix']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if ($sender->isCreative()) return self::RESULT_NOT_FOR_CREATIVE;
    $item = $sender->getInventory()->getItemInHand();
    if (!($item instanceof Durable) || $item->getDamage() <= 0) {
      $sender->sendLocalizedMessage('feature.repair-failed');
      return self::RESULT_SUCCESS;
    }
    $sender->sendLocalizedMessage('feature.repair-success');
    $item->setDamage(0);
    $sender->getInventory()->setItemInHand($item);
    return self::RESULT_SUCCESS;
  }
}
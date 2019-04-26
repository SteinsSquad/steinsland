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
use pocketmine\item\Item;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class RainbowCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('rainbow', 'feature', 'steinscore.feature.rainbow', ['ra']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    $ids = [Item::LEATHER_CAP, Item::LEATHER_TUNIC, Item::LEATHER_LEGGINGS, Item::LEATHER_BOOTS];
    $i = 0;
    foreach ($ids as $id) {
      $sender->getArmorInventory()->setItem($i++, Item::get($id, 0, 1)->setCustomName(' '));
    }
    $sender->sendLocalizedMessage('feature.rainbow-success');
    return self::RESULT_SUCCESS;
  }
}
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


class CookCommand extends CustomCommand {

  private const PROCESS_MAP = [
    Item::RAW_CHICKEN => Item::COOKED_CHICKEN,
    Item::RAW_BEEF => Item::COOKED_BEEF,
    Item::RAW_FISH => Item::COOKED_FISH,
    Item::RAW_PORKCHOP => Item::COOKED_PORKCHOP,
    Item::RAW_RABBIT => Item::COOKED_RABBIT,
    Item::RAW_MUTTON => Item::COOKED_MUTTON,
    Item::RAW_SALMON => Item::COOKED_SALMON,
    Item::POTATO => Item::BAKED_POTATO
  ];

  public function __construct() {
    parent::__construct('cook', 'feature', 'steinscore.feature.cook', ['bake']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if ($sender->isCreative()) return self::RESULT_NOT_FOR_CREATIVE;
    $item = $sender->getInventory()->getItemInHand();
    if (!isset(self::PROCESS_MAP[$item->getId()])) {
      $sender->sendLocalizedMessage('feature.cook-failed');
      return self::RESULT_SUCCESS;
    }
    $newItem = Item::get(self::PROCESS_MAP[$item->getId()], $item->getDamage(), $item->getCount());
    $sender->sendLocalizedMessage('feature.cook-success');
    $sender->getInventory()->setItemInHand($newItem);
    return self::RESULT_SUCCESS;
  }
}
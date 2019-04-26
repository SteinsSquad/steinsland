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
use steinssquad\steinscore\utils\ParseUtils;


class DupeCommand extends CustomCommand {

  private $cooldown = [];

  public function __construct() {
    parent::__construct('dupe', 'feature', 'steinscore.feature.dupe.cooldown', ['more']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if ($sender->isCreative()) return self::RESULT_NOT_FOR_CREATIVE;
    $item = $sender->getInventory()->getItemInHand();
    if ($item->getId() === Item::AIR || $item->getCount() === $item->getMaxStackSize()) {
      $sender->sendLocalizedMessage('feature.dupe-failed');
      return self::RESULT_SUCCESS;
    }
    if (!($sender->hasPermission('steinscore.feature.dupe'))) {
      if (isset($this->cooldown[$sender->getLowerCaseName()]) && $this->cooldown[$sender->getLowerCaseName()] > time()) {
        $sender->sendLocalizedMessage('generic.cooldown', ['time' => ParseUtils::placeholderFromTimestamp($this->cooldown[$sender->getLowerCaseName()] - time(), $sender)]);
        return self::RESULT_SUCCESS;
      }
      $this->cooldown[$sender->getLowerCaseName()] = time() + 300;
    }
    $item->setCount($item->getMaxStackSize() > 1 ? (
      $sender->hasPermission('steinscore.feature.dupe') ? $item->getMaxStackSize() : (($c = ceil($item->getMaxStackSize() / 2)) <= $item->getCount() ? $item->getMaxStackSize() : $c)
    ) : $item->getMaxStackSize());
    $sender->getInventory()->setItemInHand($item);
    $sender->sendLocalizedMessage('feature.dupe-success');
    return self::RESULT_SUCCESS;
  }
}
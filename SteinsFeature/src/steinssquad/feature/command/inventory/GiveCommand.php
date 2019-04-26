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
use pocketmine\item\ItemFactory;
use pocketmine\nbt\JsonNbtParser;
use pocketmine\nbt\tag\CompoundTag;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class GiveCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('give', 'feature', 'steinscore.feature.give');
    $this->registerOverload(
      ['name' => 'player', 'type' => 'player'],
      ['name' => 'itemName', 'type' => 'string', 'enum' => ['name' => 'Item', 'values' => []]],
      ['name' => 'amount', 'type' => 'int', 'optional' => true],
      ['name' => 'components', 'type' => 'json', 'optional' => true]
    );
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) < 2) return self::RESULT_USAGE;
    $player = SteinsPlayer::getPlayerByName(array_shift($args));
    if (!($player instanceof SteinsPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
    $itemName = array_shift($args);
    try {
      $item = ItemFactory::fromString($itemName);
    } catch (\InvalidArgumentException $e) {
      $sender->sendMessage($this->module('give-failed', ['item' => $itemName]));
      return self::RESULT_SUCCESS;
    }
    $item->setCount(abs(intval(array_shift($args) ?? $item->getMaxStackSize())));
    if (isset($args[3])) {
      $tags = $exception = null;
      $data = implode(" ", array_slice($args, 3));
      try {
        $tags = JsonNbtParser::parseJson($data);
      } catch (\Exception $ex) {
        $exception = $ex;
      }
      if (!($tags instanceof CompoundTag) || $exception !== null) return self::RESULT_USAGE;
      $item->setNamedTag($tags);
    }
    $player->getInventory()->addItem(clone $item);
    $sender->sendMessage($this->module('give-success', [
      'player' => $player->getCurrentName(),
      'item' =>  $item->getName() . ' (' . $item->getId() . ':' . $item->getDamage() . ')',
      'count' => $item->getCount()
    ]));
    return self::RESULT_SUCCESS;
  }
}
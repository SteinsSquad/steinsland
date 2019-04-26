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
use pocketmine\item\Armor;
use pocketmine\item\Food;
use pocketmine\item\ItemBlock;
use pocketmine\item\Tool;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class StackCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('stack', 'feature', 'steinscore.feature.stack', ['sort']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if ($sender->isCreative()) return self::RESULT_NOT_FOR_CREATIVE;
    $armors = [];
    $tools  = [];
    $blocks = [];
    $foods  = [];
    $other  = [];

    foreach ($sender->getInventory()->getContents() as $content) {
      if ($content instanceof Armor) $armors[] = $content;
      else if ($content instanceof Tool) $tools[] = $content;
      else if ($content instanceof ItemBlock) $blocks[] = $content;
      else if ($content instanceof Food) $foods[] = $content;
      else $other[] = $content;
    }
    $sender->getInventory()->clearAll();
    foreach ($armors as $armor) $sender->getInventory()->addItem($armor);
    foreach ($tools as $tool) $sender->getInventory()->addItem($tool);
    foreach ($blocks as $block) $sender->getInventory()->addItem($block);
    foreach ($foods as $food) $sender->getInventory()->addItem($food);
    foreach ($other as $item) $sender->getInventory()->addItem($item);
    $sender->sendLocalizedMessage('feature.stack-success');
    return self::RESULT_SUCCESS;
  }

}
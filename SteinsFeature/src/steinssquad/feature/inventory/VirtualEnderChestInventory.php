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

namespace steinssquad\feature\inventory;


use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\Player;
use steinssquad\steinscore\inventory\VirtualInventory;
use steinssquad\steinscore\player\SteinsPlayer;


class VirtualEnderChestInventory extends VirtualInventory {

  private $realEnderChestInventory;

  public function __construct(SteinsPlayer $holder) {
    $this->realEnderChestInventory = $holder->getEnderChestInventory();
    parent::__construct($holder, Item::ENDER_CHEST, $this->realEnderChestInventory->getContents());
  }

  public function getName(): string {
    return "EnderChest";
  }

  public function getDefaultSize(): int {
    return 27;
  }

  public function getNetworkType() : int{
    return WindowTypes::CONTAINER;
  }

  public function onClose(Player $who): void {
    $this->realEnderChestInventory->setContents($this->getContents());
    parent::onClose($who);
  }
}
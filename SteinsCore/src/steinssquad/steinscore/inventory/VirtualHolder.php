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

namespace steinssquad\steinscore\inventory;


use pocketmine\inventory\CustomInventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\math\Vector3;
use steinssquad\steinscore\player\SteinsPlayer;


class VirtualHolder extends Vector3 implements InventoryHolder{

  private $inventory;
  public function __construct(SteinsPlayer $holder, CustomInventory $inventory){
    $this->inventory = $inventory;
    parent::__construct($holder->getFloorX(), $holder->getFloorY() + 2, $holder->getFloorZ());
  }

  public function getInventory(){
    return $this->inventory;
  }

}
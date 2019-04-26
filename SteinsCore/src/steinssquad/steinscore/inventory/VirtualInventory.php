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


use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\inventory\CustomInventory;
use pocketmine\nbt\LittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\BlockEntityDataPacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\Player;
use steinssquad\steinscore\player\SteinsPlayer;


abstract class VirtualInventory extends CustomInventory {

  protected $block;


  public function __construct(SteinsPlayer $holder, int $block, array $items = []){
    $this->block = Block::get($block)->getRuntimeId();
    parent::__construct(new VirtualHolder($holder, $this), $items);
  }


  public function onOpen(Player $who): void {
    /** @var SteinsPlayer $who */
    $this->holder = $holder = new VirtualHolder($who, $this);
    $pk = new UpdateBlockPacket();
    $pk->x = $holder->getFloorX();
    $pk->y = $holder->getFloorY();
    $pk->z = $holder->getFloorZ();
    $pk->blockRuntimeId = $this->block;
    $pk->flags = UpdateBlockPacket::FLAG_ALL;
    $who->dataPacket($pk);

    $pk = new BlockEntityDataPacket();
    $pk->x = $holder->x;
    $pk->y = $holder->y;
    $pk->z = $holder->z;
    $pk->namedtag = (new LittleEndianNBTStream())->write(new CompoundTag("", [
        new StringTag("id", $this->getName()),
        new IntTag("x", $holder->getFloorX()),
        new IntTag("y", $holder->getFloorY()),
        new IntTag("z", $holder->getFloorZ())
      ]));;
    $who->dataPacket($pk);
    parent::onOpen($who);
    $this->sendContents($who);
  }


  /**
   * @param \pocketmine\Player $who
   */
  public function onClose(Player $who): void {
    $holder = $this->holder;
    $pk = new UpdateBlockPacket();
    $pk->x = $holder->getFloorX();
    $pk->y = $holder->getFloorY();
    $pk->z = $holder->getFloorZ();
    $pk->blockRuntimeId = BlockFactory::toStaticRuntimeId(
      $who->getLevel()->getBlockIdAt($holder->x, $holder->y, $holder->z),
      $who->getLevel()->getBlockDataAt($holder->x, $holder->y, $holder->z)
    );
    $pk->flags = UpdateBlockPacket::FLAG_ALL;
    $who->dataPacket($pk);
    parent::onClose($who);
  }
}
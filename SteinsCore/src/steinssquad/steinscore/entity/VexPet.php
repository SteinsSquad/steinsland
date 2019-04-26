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

namespace steinssquad\steinscore\entity;


use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\Player;
use steinssquad\steinscore\player\SteinsPlayer;


class VexPet extends Entity {

  public const NETWORK_ID = self::VEX;

  private $player;

  public function __construct(SteinsPlayer $player) {
    parent::__construct($player->getLevel(), self::createBaseNBT($player->asVector3()));
    $this->player = $player;
    $this->width = 0.4;
    $this->height = 0.8;
  }

  public function onCollideWithPlayer(Player $player): void {
    if ($this->player !== $player)
      $player->attack(new EntityDamageByEntityEvent($this->player, $player, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, 3));
  }


}
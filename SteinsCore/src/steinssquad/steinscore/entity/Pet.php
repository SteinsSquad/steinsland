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
use pocketmine\math\Math;
use pocketmine\math\Vector3;
use steinssquad\steinscore\player\SteinsPlayer;


abstract class Pet extends Entity {

  protected $player;
  /** @var SteinsPlayer */
  protected $target;

  public function __construct(SteinsPlayer $player) {
    parent::__construct($player->getLevel(), Entity::createBaseNBT($player->asPosition()));
    $this->player = $player;
  }

  public function saveNBT(): void {}

  public abstract function getViewRange(): int;

  public abstract function getSpeed(): int;

  public function setTarget(?SteinsPlayer $player) {
    $this->target = $player;
  }

  public function updateMove() {
    if (is_null($this->target)) {
      $x = $this->player->x - $this->x;
      $z = $this->player->z - $this->z;
    } else {
      $x = $this->target->x - $this->x;
      $z = $this->target->z - $this->z;
    }
    if ($x ** 2 + $z ** 2 < 4) {
      $this->motion = new Vector3(-$x, 0, -$z);
      return false;
    }
    $diff = abs($x) + abs($z);
    $this->motion->x = $this->getSpeed() * 0.15 * ($x / $diff);
    $this->motion->z = $this->getSpeed() * 0.15 * ($z / $diff);
    $this->yaw = -atan2($this->motion->x, $this->motion->z) * 180 / M_PI;
    $y = (is_null($this->target) ? $this->player->y : $this->target->y) - $this->y;
    $this->pitch = $y == 0 ? 0 : rad2deg(-atan2($y, sqrt($x ** 2 + $z ** 2)));
    $dx = $this->motion->x;
    $dz = $this->motion->z;
    $newX = Math::floorFloat($this->x + $dx);
    $newZ = Math::floorFloat($this->z + $dz);
    $block = $this->level->getBlock(new Vector3($newX, Math::floorFloat($this->y), $newZ));
    if (!$block->canBeFlowedInto()) {
      $this->motion->y += 1.1;
    } else {
      $block = $this->level->getBlock(new Vector3($newX, Math::floorFloat($this->y - 1), $newZ));
      if (!($block->canBeFlowedInto())) {
        $blockY = Math::floorFloat($this->y);
        if ($this->y - $this->gravity * 4 > $blockY) {
          $this->motion->y = -$this->gravity * 4;
        } else {
          $this->motion->y = ($this->y - $blockY) > 0 ? ($this->y - $blockY) : 0;
        }
      } else {
        $this->motion->y -= $this->gravity * 4;
      }
    }
    $dy = $this->motion->y;
    $this->move($dx, $dy, $dz);
    $this->updateMovement();
    return true;
  }

  public function onUpdate(int $currentTick): bool {
    if ($this->closed) {
      return false;
    } else if ($this->player === null || $this->player->isOnline()) {
      $this->close();
      return false;
    } else if (!$this->player->isAlive()) {
      return false;
    }
    if ($this->distance($this->player) >= $this->getViewRange()) $this->teleport($this->player->asPosition());

    $this->updateMove();
    return true;
  }
}
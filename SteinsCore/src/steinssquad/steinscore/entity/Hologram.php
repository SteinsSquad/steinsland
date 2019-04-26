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
use pocketmine\entity\Human;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Position;
use pocketmine\utils\TextFormat;
use steinssquad\steinscore\player\SteinsPlayer;


class Hologram extends Human {

  /** @var Hologram[] */
  public static $holograms = [];

  public static function getHologram(Position $position): ?Hologram {
    $neared = null;
    $dist = PHP_INT_MAX;
    foreach (self::$holograms as $hologram) {
      if ($hologram->distance($position) < $dist) {
        $neared = $hologram;
        $dist = $hologram->distance($position);
      }
    }
    return $dist > 3 ? null : $neared;
  }

  /**
   * @return Hologram[]
   */
  public static function getHolograms(): array {
    return self::$holograms;
  }

  public $hologramID;

  private $title;
  private $text;
  private $creator;

  public function __construct(Position $pos, string $title, $text = "", string $creator = null) {
    $this->hologramID = md5($pos->__toString());
    $this->skin = new Skin("Standard_Custom", str_repeat("\x00", 8192));
    parent::__construct($pos->level, Entity::createBaseNBT($pos));
    $this->setNameTagVisible();
    $this->setNameTagAlwaysVisible();
    $this->setImmobile();
    $this->setScale(0.001);

    $this->title = $title;
    $this->text = $text;
    $this->creator = $creator;
    self::$holograms[$this->hologramID] = $this;
  }

  public function onUpdate(int $currentTick): bool {
    $this->lastUpdate = $currentTick;
    foreach (SteinsPlayer::getOnlinePlayers() as $player) {
      if ($player->getLevel() === $this->getLevel() && $player->distance($this) <= 16) $this->sendData($player, [self::DATA_NAMETAG => [self::DATA_TYPE_STRING, $this->getText($player)]]);
    }
    return true;
  }

  public function getCurrentText(SteinsPlayer $player): ?string {
    if (is_array($this->text)) {
      if ($this->lastUpdate % 160 === 0) next($this->text);
      $ret = current($this->text) ?: reset($this->text);
      if (isset($ret{0}) && $ret{0} === '%') $ret = $player->localize(substr($ret, 1));

      return $ret;
    }
    return $this->text;
  }

  public function getText(SteinsPlayer $player) {
    $placeholders = [
      'player' => $player->getCurrentName(),
      'money' => $player->getMoney(),
      'prefix' => $player->getPrefix(),
      'group' => $player->getGroup()->getPrefix(),
      'x' => round($player->getX(), 3),
      'y' => round($player->getY(), 3),
      'z' => round($player->getZ(), 3)
    ];
    if (isset($this->title{0}) && $this->title{0} === '%') {
      return $player->localize(substr($this->title, 1), array_merge(['text' => $this->getCurrentText($player)], $placeholders));
    }
    return TextFormat::colorize(str_replace(
      array_map(function(string $key) {return "{{$key}}";}, array_keys($placeholders)),
      array_values($placeholders),
      $this->title . (empty($this->text) ? '' : "\n" . ($this->getCurrentText($player)))));
  }

  public function getCreator(): ?string {
    return $this->creator;
  }

  public function isCreator(SteinsPlayer $player) {
    return $this->creator !== null && $player->getLowerCaseName() === $this->creator;
  }

  public function attack(EntityDamageEvent $source): void {}

  public function close(): void {
    parent::close();
    unset(self::$holograms[$this->hologramID]);
  }

  public function saveNBT(): void {}
}
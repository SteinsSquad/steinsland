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

namespace steinssquad\feature\task;


use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\steinscore\utils\GlobalSettings;
use steinssquad\steinscore\utils\Percentage;


class AutoMineTask extends Task {

  private $blocks;
  private $center;

  public function __construct() {
    $this->blocks = new Percentage(
      [
        ['chance' => 30, 'item' => Block::STONE],
        ['chance' => 25, 'item' => Block::COBBLESTONE],
        ['chance' => 20, 'item' => Block::COAL_ORE],
        ['chance' => 10, 'item' => Block::IRON_ORE],
        ['chance' => 4, 'item' => Block::GOLD_ORE],
        ['chance' => 10, 'item' => Block::WOOD],
        ['chance' => 1, 'item' => Block::DIAMOND_ORE]
      ]);

   $this->center = new Vector3(
      (GlobalSettings::get('mine')['max'][0] + GlobalSettings::get('mine')['min'][0]) / 2,
     GlobalSettings::get('mine')['max'][1] + 1,
      (GlobalSettings::get('mine')['max'][2] + GlobalSettings::get('mine')['min'][2]) / 2
   );
  }

  public function onRun(int $currentTick) {
    for ($x = GlobalSettings::get('mine')['min'][0]; $x <= GlobalSettings::get('mine')['max'][0]; $x++) {
      for ($y = GlobalSettings::get('mine')['min'][1]; $y <= GlobalSettings::get('mine')['max'][1]; $y++) {
        for ($z = GlobalSettings::get('mine')['min'][2]; $z <= GlobalSettings::get('mine')['max'][2]; $z++) {
          Server::getInstance()->getDefaultLevel()->setBlockIdAt($x, $y, $z, $this->blocks->nextRandom());
        }
      }
    }
    /** @var SteinsPlayer $player */
    foreach (Server::getInstance()->getOnlinePlayers() as $player) {
      if (
        $player->getLevel() === Server::getInstance()->getDefaultLevel() &&
        $player->getX() >= GlobalSettings::get('mine')['min'][0] && $player->getX() <= GlobalSettings::get('mine')['max'][0] &&
        $player->getZ() >= GlobalSettings::get('mine')['min'][2] && $player->getZ() <= GlobalSettings::get('mine')['max'][2]
      ) $player->teleport($this->center);
      $player->sendLocalizedMessage('feature.automine-regen');
    }

  }
}
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


use pocketmine\scheduler\Task;
use pocketmine\Server;
use steinssquad\feature\SteinsFeature;
use steinssquad\steinscore\player\SteinsPlayer;


class ItemCleanerNotifyTask extends Task {

  private $cleaner;

  public function __construct() {
    $this->cleaner = new ItemCleanerTask();
  }

  public function onRun(int $currentTick) {
    /** @var SteinsPlayer $player */
    foreach (Server::getInstance()->getOnlinePlayers() as $player) $player->sendLocalizedMessage('feature.citem-soon');
    SteinsFeature::$instance->getScheduler()->scheduleDelayedTask($this->cleaner, 20 * 15);
  }
}
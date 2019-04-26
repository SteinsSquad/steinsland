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


use pocketmine\entity\object\ItemEntity;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use steinssquad\steinscore\player\SteinsPlayer;


class ItemCleanerTask extends Task {

  public function onRun(int $currentTick) {
    $i = 0;
    foreach (Server::getInstance()->getLevels() as $level) {
      foreach ($level->getEntities() as $entity) {
        if (!($entity instanceof ItemEntity)) continue;
        $entity->close();
        $i++;
      }
    }
    /** @var SteinsPlayer $player */
    foreach (Server::getInstance()->getOnlinePlayers() as $player) $player->sendLocalizedMessage('feature.citem-success', ['count' => $i]);
  }
}
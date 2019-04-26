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

namespace steinssquad\feature\command\helpful;


use pocketmine\command\CommandSender;
use pocketmine\Server;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class ListCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('list', 'feature', 'steinscore.feature.list', ['players']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    $sender->sendMessage($this->translate('feature.list-header', ['count' => count($players = Server::getInstance()->getOnlinePlayers())]));
    $groups = [];
    /** @var SteinsPlayer $player */
    foreach ($players as $player) {
      if ($player->getGroup() === null) continue;
      if (empty($groups[$player->getGroup()->getName()])) $groups[$player->getGroup()->getName()] = [];
      $groups[$player->getGroup()->getName()][] = $player->getCurrentName();
    }
    foreach ($groups as $group => $players) {
      $sender->sendMessage($this->translate('feature.list-group-order', ['group' => $group, 'players' => implode("&f, &a", $players), 'count' => count($players)]));
    }
    return self::RESULT_SUCCESS;
  }
}
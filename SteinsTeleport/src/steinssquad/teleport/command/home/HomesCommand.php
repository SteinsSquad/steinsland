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

namespace steinssquad\teleport\command\home;


use pocketmine\command\CommandSender;
use pocketmine\IPlayer;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\teleport\SteinsTeleport;


class HomesCommand extends CustomCommand {


  public function __construct() {
    parent::__construct('homes', 'teleport', 'steinscore.teleport.home.use');
    $this->registerPermissibleOverload(
      ['steinscore.teleport', 'steinscore.teleport.home'],
      ['name' => 'player', 'type' => 'player', 'optional' => true]
    );
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) === 0 && !$sender instanceof SteinsPlayer) return self::RESULT_IN_GAME;
    $player = $sender;
    if (count($args) > 0) {
      if (!$sender->hasPermission('steinscore.teleport.home')) return self::RESULT_NO_RIGHTS;
      $player = SteinsPlayer::getOfflinePlayer(array_shift($args));
      if (!($player instanceof IPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
    }
    $homes = SteinsTeleport::$instance->getHomes($player);
    $sender->sendMessage($this->module('homes-header', ['player' => SteinsPlayer::getPlayerName($player), 'count' => count($homes)]));
    foreach ($homes as $name => $position) {
      $sender->sendMessage($this->module('homes-line', [
        'name' => $name,
        'x' => $position->getX(),
        'y' => $position->getY(),
        'z' => $position->getZ(),
        'level' => $position->getLevel()->getFolderName()
      ]));
    }
    return self::RESULT_SUCCESS;
  }

}
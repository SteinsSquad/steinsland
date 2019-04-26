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
use pocketmine\level\Position;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\teleport\SteinsTeleport;


class HomeCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('home', 'teleport', 'steinscore.teleport.home.use', ['h']);
    $this->registerOverload(['name' => 'home', 'type' => 'string']);
    $this->registerPermissibleOverload(
      ['steinscore.teleport', 'steinscore.teleport.home'],
      ['name' => 'player', 'type' => 'player'],
      ['name' => 'home', 'type' => 'string']
    );
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    $homeName = array_shift($args);
    if (empty($homeName)) return self::RESULT_USAGE;

    $player = $sender;
    if (count($args) > 0) {
      if (!$sender->hasPermission('steinscore.teleport.home')) return self::RESULT_NO_RIGHTS;
      $player = SteinsPlayer::getOfflinePlayer($homeName);
      if (!($player instanceof IPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
      $homeName = array_shift($args);
    }
    $position = SteinsTeleport::$instance->getHome($player, $homeName);
    if ($position === null) {
      $sender->sendLocalizedMessage('teleport.home-not-found', ['home' => $homeName]);
      return self::RESULT_SUCCESS;
    }
    $sender->addTask(function(SteinsPlayer $player, Position $target) {
      $player->sendLocalizedPopup('teleport.teleporting');
      $player->teleport($target);
    }, $seconds = $sender->getTeleportCooldown(), $position);
    $sender->sendLocalizedMessage(
      $sender === $player ?
        'teleport.home-success' . ($seconds > 0 ? '-wait' : '') :
        'teleport.home-success-player', ['seconds' => $seconds, 'home' => $homeName, 'player' => SteinsPlayer::getPlayerName($player)]);
    return self::RESULT_SUCCESS;
  }

}
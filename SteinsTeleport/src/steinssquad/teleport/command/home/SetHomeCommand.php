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
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class SetHomeCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('sethome', 'teleport', 'steinscore.teleport.home.use', ['sh']);
    $this->registerOverload(['name' => 'home', 'type' => 'string']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (count($args) === 0) return self::RESULT_USAGE;

    $isUpdate = isset($sender->getHomes()[$homeName = strtolower(array_shift($args))]);
    if (!$isUpdate) {
      $homes = 1;
      if ($sender->hasPermission('steinscore.teleport.homes')) $homes = 10;
      else if ($sender->hasPermission('steinscore.teleport.homes.5')) $homes = 5;
      else if ($sender->hasPermission('steinscore.teleport.homes.3')) $homes = 3;


      if (count($sender->getHomes()) >= $homes) {
        $sender->sendLocalizedMessage('teleport.sethome-too-many-homes', ['count' => $homes]);
        return self::RESULT_SUCCESS;
      }
    }
    if ($sender->setHome($homeName)) {
      $sender->sendLocalizedMessage($isUpdate ? 'teleport.sethome-success-updated' : 'teleport.sethome-success', ['home' => $homeName]);
      return self::RESULT_SUCCESS;
    }
    $sender->sendLocalizedMessage('teleport.sethome-failed');
    return self::RESULT_SUCCESS;
  }
}
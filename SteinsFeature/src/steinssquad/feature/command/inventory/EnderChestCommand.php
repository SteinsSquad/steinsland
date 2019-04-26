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

namespace steinssquad\feature\command\inventory;


use pocketmine\command\CommandSender;
use steinssquad\feature\inventory\VirtualEnderChestInventory;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class EnderChestCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('enderchest', 'feature','steinscore.feature.enderchest.use', ['ec']);
    $this->registerPermissibleOverload(['steinscore.feature', 'steinscore.enderchest'], ['name' => 'player', 'type' => 'player', 'optional' => true]);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    $player = $sender;
    if (count($args) > 0) {
      if (!($sender->hasPermission('steinscore.feature.enderchest'))) return self::RESULT_NO_RIGHTS;
      if (!(($player = SteinsPlayer::getPlayerByName(array_shift($args))) instanceof SteinsPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
    }
    $sender->sendLocalizedMessage($player === $sender ? 'feature.enderchest-success' : 'feature.enderchest-success-player', ['player' => $player->getCurrentName()]);
    $sender->addWindow(new VirtualEnderChestInventory($player));
    return self::RESULT_SUCCESS;
  }
}
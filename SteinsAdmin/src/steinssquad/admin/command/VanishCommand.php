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

namespace steinssquad\admin\command;


use pocketmine\command\CommandSender;
use pocketmine\Server;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class VanishCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('vanish', 'admin', 'steinscore.admin.vanish', ['v']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    $sender->vanish = !$sender->vanish;
    foreach (Server::getInstance()->getOnlinePlayers() as $player) {
      if ($sender->vanish && !$player->canSee($sender)) $player->hidePlayer($sender);
      else if (!$sender->vanish) $player->showPlayer($sender);
    }
    $sender->sendLocalizedMessage($sender->vanish ? 'admin.vanish-success-on' : 'admin.vanish-success-off');
    return self::RESULT_SUCCESS;
  }
}
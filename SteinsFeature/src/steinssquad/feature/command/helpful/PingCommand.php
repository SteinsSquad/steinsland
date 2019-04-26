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
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class PingCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('ping', 'feature','steinscore.feature.ping');
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    $sender->sendLocalizedMessage('feature.ping-success', ['ping' => $sender->getPing()]);
    return self::RESULT_SUCCESS;
  }
}
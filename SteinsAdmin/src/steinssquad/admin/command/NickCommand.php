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
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class NickCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('nick', 'admin', 'steinscore.admin.nick');

    $this->registerOverload(['name' => 'nick', 'type' => 'string', 'optional' => true]);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    $name = null;
    if (count($args) > 0) {
      $name = array_shift($args);
      if ($name === 'clear') $name = null;
      else if (!(SteinsPlayer::isValidUserName($name))) return self::RESULT_USAGE;
    }
    $sender->setFakeName($name);
    $sender->sendLocalizedMessage($name === null ? 'admin.nick-success-remove' : 'admin.nick-success-set', ['nick' => $name]);
    return self::RESULT_SUCCESS;
  }
}
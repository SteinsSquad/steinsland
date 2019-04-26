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

namespace steinssquad\clan\command;


use pocketmine\command\CommandSender;
use steinssquad\clan\SteinsClan;
use steinssquad\steinscore\command\CustomCommand;


class ClanTopCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('ctop', 'clan', 'steinscore.clan.create');
  }

  public function onCommand(CommandSender $sender, array $args) {
    var_dump(SteinsClan::$instance->getClanTop(5));
  }
}
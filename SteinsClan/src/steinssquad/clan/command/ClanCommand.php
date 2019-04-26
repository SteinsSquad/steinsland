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
use steinssquad\steinscore\command\CustomCommand;


class ClanCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('clan', 'clan', 'steinscore.clan.create', ['c']);

    $this->registerOverload(
      ['type' => 'rawtext', 'name' => 'action', 'enum' => [
        'name' => 'actions',
        'values' => [/*'info', 'top',*/ 'upgrade']
      ]]
    );
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) === 0) return self::RESULT_USAGE;
    $action = array_shift($args);
    switch ($action) {
      case 'info':
        return self::RESULT_SUCCESS;
      case 'top':
        return self::RESULT_SUCCESS;
      case 'upgrade':
        return self::RESULT_SUCCESS;
    }
    return self::RESULT_USAGE;
  }
}
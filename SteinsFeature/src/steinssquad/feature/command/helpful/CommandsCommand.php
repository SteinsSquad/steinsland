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


use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use steinssquad\steinscore\command\CustomCommand;


class CommandsCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('commands', 'feature', 'steinscore.feature.commands');
    $this->registerOverload(['name' => 'page', 'type' => 'int', 'optional' => true]);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    $commands = [];
    foreach (Server::getInstance()->getCommandMap()->getCommands() as $command) {
      if ($command->testPermissionSilent($sender)) $commands[$command->getName()] = $command;
    }
    $page = array_shift($args) ?? 1;
    if (!(is_numeric($page))) return self::RESULT_USAGE;

    ksort($commands, SORT_NATURAL | SORT_FLAG_CASE);
    $commands = array_chunk($commands, $sender->getScreenLineHeight());
    $page = max(1, min(intval($page), count($commands)));
    $sender->sendMessage($this->module('commands-header', ['page' => $page, 'count' => count($commands)]));
    /** @var Command $command */
    foreach ($commands[$page - 1] as $command) {
      if ($command instanceof CustomCommand) {
        $usages = $command->getUsages();
        if (count($usages) > 0) {
          if (count($usages) > 1) $sender->sendMessage($this->module('commands-line-header', ['command' => $command->getName(), 'description' => $this->translate($command->getDescription())]));
          foreach ($usages as $usage) {
            $hasPermission = count($usage['permissions']) === 0;
            foreach ($usage['permissions'] ?? [] as $permission) {
              if ($sender->hasPermission($permission)) $hasPermission = true;
            }
            if ($hasPermission) $sender->sendMessage($this->module(
              count($usages) > 1 ? 'commands-line-sub' : 'commands-line', [
                'usage' => $usage['usage'],
                'command' => $command->getName(),
                'description' => $this->translate($command->getDescription())
              ]));
          }
        } else $sender->hasPermission($this->module('commands-line', [
          'command' => $command->getName(),
          'usage' => '',
          'description' => $this->translate($command->getDescription())
        ]));
      } else $sender->sendMessage($this->module('commands-line', ['command' => $command->getName(), 'usage' =>  '', 'description' => '']));

    }
    return self::RESULT_SUCCESS;
  }
}
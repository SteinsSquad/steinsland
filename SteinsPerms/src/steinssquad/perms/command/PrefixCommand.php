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

namespace steinssquad\perms\command;


use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use steinssquad\perms\model\Group;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class PrefixCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('prefix', 'permission', 'steinscore.permission.prefix.use');

    $this->registerOverload(['name' => 'group', 'type' => 'rawtext', 'enum' => ['values' => array_merge(['clear'], array_keys(Group::getGroups())), 'name' => 'groups'], 'optional' => true]);

    $this->registerPermissibleOverload(
      ['steinscore.permission.prefix'],
      ['name' => 'set', 'type' => 'rawtext', 'enum' => ['values' => ['set'], 'name' => 'sets'], 'optional' => true],
      ['name' => 'prefix', 'type' => 'rawtext', 'optional' => true]);
  }

  public function onCommand(CommandSender $sender, array $args) {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    $prefix = null;
    $suffix = null;
    if (count($args) > 0) {
      $action = array_shift($args);
      if (Group::getGroup(strtolower($action)) instanceof Group) {
        if (Group::getGroup(strtolower($action))->getPriority() > $sender->getGroup()->getPriority()) return self::RESULT_NO_RIGHTS;
        $prefix = Group::getGroup(strtolower($action))->getPrefix();
        $suffix = Group::getGroup(strtolower($action))->getSuffix();
      } else if ($action === 'set') {
        if (!($sender->hasPermission('steinscore.permission.prefix'))) return self::RESULT_NO_RIGHTS;
        if (count($args) === 0) return self::RESULT_USAGE;
        $prefix = implode(" ", $args);
        if (mb_strlen(TextFormat::clean($prefix, true)) < 3 || mb_strlen(TextFormat::clean($prefix, true)) >= 12) return self::RESULT_USAGE;
      } else if ($prefix === 'clear') {
        $prefix = null;
        $suffix = null;
      }
    }
    $sender->setPrefix($prefix);
    $sender->setSuffix($suffix);
    $sender->sendLocalizedMessage($prefix === null ? 'permission.prefix-success-remove' : 'permission.prefix-success-set', ['prefix' => $prefix]);
    return self::RESULT_SUCCESS;
  }
}
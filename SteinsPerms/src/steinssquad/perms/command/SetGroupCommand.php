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
use pocketmine\IPlayer;
use steinssquad\perms\model\Group;
use steinssquad\perms\SteinsPerms;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\steinscore\utils\ParseUtils;


class SetGroupCommand extends CustomCommand {

  public function __construct() {
    parent::__construct("setgroup", "permission", 'steinscore.permission.setgroup');
    $this->registerOverload(
      ['name' => 'player', 'type' => 'player'],
      ['name' => 'group', 'type' => 'rawtext', 'enum' => ['name' => 'groups', 'values' => array_keys(SteinsPerms::$instance->getConfig()->get('groups'))]],
      ['name' => 'time', 'type' => 'rawtext', 'optional' => true]
    );
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) < 2) return self::RESULT_USAGE;
    $player = SteinsPlayer::getOfflinePlayerExact(array_shift($args));
    if (!($player instanceof IPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
    if (is_null($group = Group::getGroup(array_shift($args)))) {
      $sender->sendMessage($this->module('setgroup-group-error'));
      return self::RESULT_SUCCESS;
    }
    $until = null;
    if (count($args) > 0) $until = ParseUtils::timestampFromString(implode($args));

    if (!($sender instanceof SteinsPlayer)) {
      $oldGroup = SteinsPerms::$instance->getGroup($player);
      if ($group->getPriority() < $oldGroup->getPriority() || ($group->getPriority() === $oldGroup->getPriority() && $until !== null)) {
        $sender->sendMessage($this->module('setgroup-priority-error', ['player' => $player->getName()]));
        return self::RESULT_SUCCESS;
      }
    }
    SteinsPerms::$instance->setGroup($player, $group, $until);
    $sender->sendMessage($this->module(
      is_null($until) ?
        'setgroup-success' :
        'setgroup-success-temporary',
      ['player' => $player->getName(), 'group' => $group->getName(), 'time' => ParseUtils::placeholderFromTimestamp($until ?? 0, $sender)]
    ));
    if ($player instanceof SteinsPlayer) {
      $player->sendLocalizedMessage(
        is_null($until) ?
          'permission.setgroup-changed' :
          'permission.setgroup-changed-temporary',
        ['group' => $group->getName(), 'time' => ParseUtils::placeholderFromTimestamp($until ?? 0, $player)]
      );
    }
    return self::RESULT_SUCCESS;
  }

}
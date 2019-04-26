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


class KillCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('kill', 'admin', 'steinscore.admin.kill.use', ['slain', 'suicide']);
    $this->registerPermissibleOverload(['steinscore.admin.kill'], ['name' => 'player', 'type' => 'player', 'optional' => true]);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) === 0 && !($sender instanceof SteinsPlayer)) return self::RESULT_USAGE;
    $target = $sender;
    if (count($args) > 0) {
      if (!($sender->hasPermission('steinscore.admin.kill'))) return self::RESULT_NO_RIGHTS;
      $target = SteinsPlayer::getPlayerByName(array_shift($args));
      if (!($target instanceof SteinsPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
    }
    if ($sender instanceof SteinsPlayer && $target !== $sender && $target->getGroup()->getPriority() > $sender->getGroup()->getPriority()) return self::RESULT_PLAYER_HAS_IMMUNITY;
    $target->kill();
    $sender->sendMessage($this->translate($sender === $target ? 'admin.kill-yourself-success' : 'admin.kill-success', ['player' => $target->getCurrentName()]));
    if ($target !== $sender) $target->sendLocalizedMessage('admin.kill-you-slain', ['player' => $sender instanceof SteinsPlayer? $sender->getCurrentName(): $sender->getName()]);
    return self::RESULT_SUCCESS;
  }
}
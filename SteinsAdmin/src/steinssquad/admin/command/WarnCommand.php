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
use steinssquad\steinscore\utils\ParseUtils;


class WarnCommand extends CustomCommand {

  private $warnCooldown = [];
  private $warns = [];

  public function __construct() {
    parent::__construct('warn', 'admin', 'steinscore.admin.warn');
    $this->registerOverload(['name' => 'player', 'type' => 'player'], ['name' => 'reason', 'type' => 'message']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) < 2) return self::RESULT_USAGE;
    $player = SteinsPlayer::getPlayerByName(array_shift($args));
    if (!($player instanceof SteinsPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
    if (isset($this->kickCooldown[strtolower($sender->getName())]) && $this->warnCooldown[strtolower($sender->getName())] > time()) {
      $sender->sendMessage($this->translate('generic.cooldown', [
        'time' => ParseUtils::placeholderFromTimestamp($this->warnCooldown[strtolower($sender->getName())] - time(), $sender)
      ]));
      return self::RESULT_SUCCESS;
    }
    $this->warnCooldown[strtolower($sender->getName())] = time() + 300;
    if (!isset($this->warns[$player->getLowerCaseName()])) $this->warns[$player->getLowerCaseName()] = 0;
    if (++$this->warns[$player->getLowerCaseName()] === 3) {
      $player->kick($player->localize('admin.warn-you-kicked'), false);
      unset($this->warns[$player->getLowerCaseName()]);
    }
    $this->broadcast('admin.warn-success', ['count' => $this->warns[$player->getLowerCaseName()], 'player' => $sender instanceof SteinsPlayer ? $sender->getCurrentName() : $sender->getName(), 'target' => $player->getCurrentName(), 'reason' => implode(" ", $args)]);
    return self::RESULT_SUCCESS;
  }
}
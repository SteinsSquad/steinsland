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


class FlyCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('fly', 'feature', 'steinscore.feature.fly.use');
    $this->registerPermissibleOverload(['steinscore.feature', 'steinscore.feature.fly'], ['name' => 'player', 'type' => 'player', 'optional' => true]);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) === 0 && !($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    $target = $sender;
    if (count($args) > 0) {
      if (!($sender->hasPermission('steinscore.feature.fly'))) return self::RESULT_NO_RIGHTS;
      if (!(($target = SteinsPlayer::getPlayerByName(array_shift($args))) instanceof SteinsPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
      if ($sender instanceof SteinsPlayer && $target->getGroup()->getPriority() > $sender->getGroup()->getPriority()) return self::RESULT_PLAYER_HAS_IMMUNITY;
    }
    $target->setAllowFlight(!$target->getAllowFlight());
    if ($target !== $sender) {
      $target->sendLocalizedMessage($target->getAllowFlight() ? 'feature.fly-player-on' : 'feature.fly-player-off', ['player' => $sender instanceof SteinsPlayer? $sender->getCurrentName(): $sender->getName()]);
      $sender->sendMessage($this->translate($target->getAllowFlight() ? 'feature.fly-success-player-on' : 'feature.fly-success-player-off', ['player' => $target->getCurrentName()]));
    } else $target->sendLocalizedMessage($target->getAllowFlight() ? 'feature.fly-success-on' : 'feature.fly-success-off');
    return self::RESULT_SUCCESS;
  }
}
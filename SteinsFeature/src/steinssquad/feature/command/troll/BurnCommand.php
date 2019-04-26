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

namespace steinssquad\feature\command\troll;


use pocketmine\command\CommandSender;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class BurnCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('burn', 'feature', 'steinscore.feature.burn');
    $this->registerOverload(['name' => 'player', 'type' => 'player'], ['name' => 'seconds', 'type' => 'int', 'optional' => true]);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) === 0) return self::RESULT_USAGE;
    /** @var SteinsPlayer $player */
    if (!(($player = SteinsPlayer::getPlayerByName(array_shift($args))) instanceof SteinsPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
    if ($sender instanceof SteinsPlayer && $player->getGroup()->getPriority() > $sender->getGroup()->getPriority()) return self::RESULT_PLAYER_HAS_IMMUNITY;
    $seconds = array_shift($args) ?? 10;
    if (!(is_numeric($seconds))) return self::RESULT_USAGE;
    $seconds = abs(intval($seconds));

    $sender->sendMessage($this->translate('feature.burn-success', ['player' => $player->getCurrentName(), 'seconds' => $seconds]));
    $player->sendLocalizedMessage('feature.burn-you-burn', ['player' => $sender instanceof SteinsPlayer? $sender->getCurrentName(): $sender->getName()]);
    $player->setOnFire($seconds);
    return self::RESULT_SUCCESS;
  }
}
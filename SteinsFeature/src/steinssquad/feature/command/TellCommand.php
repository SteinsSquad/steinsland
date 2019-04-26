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

namespace steinssquad\feature\command;


use pocketmine\command\CommandSender;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class TellCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('tell', 'feature', 'steinscore.feature.tell.use', ['w', 'msg']);
    $this->registerOverload(['name' => 'player', 'type' => 'player'], ['name' => 'message', 'type' => 'message']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) < 2) return self::RESULT_USAGE;

    $player = SteinsPlayer::getPlayerByName(array_shift($args));
    if (!($player instanceof SteinsPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
    if ($sender instanceof SteinsPlayer && $player->isIgnorePlayer($sender)) return self::RESULT_PLAYER_IGNORES_YOU;
    $sender->sendMessage($this->translate('feature.tell-success', ['player' => $player->getCurrentName(), 'message' => $message = implode(' ', $args)]));
    $player->sendLocalizedMessage('feature.tell-got', ['player' => $sender instanceof SteinsPlayer? $sender->getCurrentName(): $sender->getName(), 'message' => $message]);
    $this->broadcastSubscribers('feature.tell-view', ['player' => $sender instanceof SteinsPlayer? $sender->getCurrentName(): $sender->getName(), 'target' => $player->getCurrentName(), 'message' => $message], ['steinscore.feature', 'steinscore.feature.tell']);
    return self::RESULT_SUCCESS;
  }
}
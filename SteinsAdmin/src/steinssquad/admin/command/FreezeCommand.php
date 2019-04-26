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


class FreezeCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('freeze', 'admin','steinscore.admin.freeze');
    $this->registerOverload(['name' => 'player', 'type' => 'player']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) === 0) return self::RESULT_SUCCESS;
    $player = SteinsPlayer::getPlayerByName(array_shift($args));
    if (!($player instanceof SteinsPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
    if ($sender instanceof SteinsPlayer && $player->getGroup()->getPriority() > $sender->getGroup()->getPriority()) return self::RESULT_PLAYER_HAS_IMMUNITY;
    $player->setImmobile(!$player->isImmobile());
    $sender->sendMessage($this->translate($player->isImmobile() ? 'admin.freeze-success-on' : 'admin.freeze-success-off', ['player' => SteinsPlayer::getPlayerName($player)]));
    $player->sendLocalizedMessage($player->isImmobile() ? 'admin.freeze-you-frozen' : 'admin.freeze-you-unfrozen', ['player' => $sender instanceof SteinsPlayer? $sender->getCurrentName(): $sender->getName()]);
    return self::RESULT_SUCCESS;
  }
}
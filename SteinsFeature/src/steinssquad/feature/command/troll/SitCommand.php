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
use pocketmine\network\mcpe\protocol\SetEntityLinkPacket;
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\Server;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class SitCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('sit', 'feature', 'steinscore.feature.sit');
    $this->registerOverload(['type' => 'player', 'name' => 'player']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (count($args) === 0) return self::RESULT_USAGE;
    $player = SteinsPlayer::getPlayerByName(array_shift($args));
    if (!($player instanceof SteinsPlayer) || $player === $sender) return self::RESULT_PLAYER_NOT_FOUND;
    $pk = new SetEntityLinkPacket;
    $pk->link = new EntityLink($player->getId(), $sender->getId(), EntityLink::TYPE_PASSENGER);
    Server::getInstance()->broadcastPacket(Server::getInstance()->getOnlinePlayers(), $pk);
    $sender->sendLocalizedMessage('feature.sit-success', ['player' => $player->getCurrentName()]);
    return self::RESULT_SUCCESS;
  }
}
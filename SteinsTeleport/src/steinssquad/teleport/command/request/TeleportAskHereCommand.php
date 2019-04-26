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

namespace steinssquad\teleport\command\request;


use pocketmine\command\CommandSender;
use pocketmine\level\sound\ClickSound;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\teleport\SteinsTeleport;


class TeleportAskHereCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('tpahere', 'teleport', 'steinscore.teleport.tpahere');
    $this->registerOverload(['name' => 'player', 'type' => 'player']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (count($args) === 0) return self::RESULT_USAGE;

    $player = SteinsPlayer::getPlayerByName(array_shift($args));
    if (!$player instanceof SteinsPlayer || $player === $sender) return self::RESULT_PLAYER_NOT_FOUND;
    if ($player->isIgnorePlayer($sender)) return self::RESULT_PLAYER_IGNORES_YOU;

    SteinsTeleport::$instance->addTeleportRequest($sender, $player, SteinsTeleport::TYPE_TPAHERE);
    $sender->sendLocalizedMessage('teleport.tpahere-success', ['player' => $player->getCurrentName()]);
    $player->sendLocalizedMessage('teleport.tpahere-request', ['player' => $sender->getCurrentName()]);
    $player->getLevel()->addSound(new ClickSound($player), [$player]);
    return self::RESULT_SUCCESS;
  }
}
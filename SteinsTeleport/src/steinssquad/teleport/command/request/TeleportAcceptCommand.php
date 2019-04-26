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
use pocketmine\level\Position;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\teleport\SteinsTeleport;


class TeleportAcceptCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('tpaccept', 'teleport', 'steinscore.teleport.teleport', ['tpc']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (!(SteinsTeleport::$instance->hasTeleportRequest($sender))) {
      $sender->sendLocalizedMessage('teleport.tpaccept-no-requests');
      return self::RESULT_SUCCESS;
    }
    $teleportType = SteinsTeleport::$instance->getTeleportType($sender);
    $player = SteinsPlayer::getPlayerExact(SteinsTeleport::$instance->getTeleportIssuer($sender));
    SteinsTeleport::$instance->removeTeleportRequest($sender);
    if (!($player instanceof SteinsPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
    $target = $player;
    $origin = $sender;
    $seconds = $target->getTeleportCooldown();
    if ($teleportType === SteinsTeleport::TYPE_TPAHERE) {
      $target = $sender;
      $origin = $player;
      $sender->sendLocalizedMessage($seconds > 0 ? 'teleport.tpaccept-here-success-wait' : 'teleport.tpaccept-here-success', ['seconds' => $seconds = $target->getTeleportCooldown(), 'player' => $player->getCurrentName()]);
      $player->sendLocalizedMessage('teleport.tpaccept-here-accepted', ['player' => $sender->getCurrentName()]);
    } else {
      $sender->sendLocalizedMessage('teleport.tpaccept-success', ['player' => $player->getCurrentName()]);
      $player->sendLocalizedMessage($seconds > 0 ? 'teleport.tpaccept-accepted-wait' : 'teleport.tpaccept-accepted', ['seconds' => $seconds, 'player' => $sender->getCurrentName()]);
    }
    $target->addTask(function (SteinsPlayer $player, Position $target) {
      $player->sendLocalizedPopup('teleport.teleporting');
      $player->teleport($target);
    }, $seconds, $origin->asPosition());
    return self::RESULT_SUCCESS;
  }
}
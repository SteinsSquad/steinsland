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

namespace steinssquad\clan\command;


use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use steinssquad\clan\SteinsClan;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class ClanHomeCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('chome', 'clan', 'steinscore.clan.create');
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (is_null($clanName = SteinsClan::$instance->getPlayerClan($sender))) {
      $sender->sendLocalizedMessage('clan.clan-not-found');
      return self::RESULT_SUCCESS;
    }
    if (($home = SteinsClan::$instance->getHome($clanName)) === null) {
      $sender->sendLocalizedMessage('clan.chome-failed');
      return self::RESULT_SUCCESS;
    }
    $sender->addTask(function(SteinsPlayer $player, Position $home) {
      $player->sendLocalizedPopup('teleport.teleporting');
      $player->teleport($home);
    }, $seconds = $sender->getTeleportCooldown(), $home);
    $sender->sendLocalizedMessage($seconds > 0 ? 'clan.chome-success-wait' : 'clan.chome-success', ['clan' => $clanName, 'seconds' => $seconds]);
    return self::RESULT_SUCCESS;
  }
}
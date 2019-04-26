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
use steinssquad\clan\SteinsClan;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class ClanInviteCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('cinvite', 'clan', 'steinscore.clan.create');
    $this->registerOverload(['name' => 'player', 'type' => 'player']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (is_null($clanName = SteinsClan::$instance->getPlayerClan($sender))) {
      $sender->sendLocalizedMessage('clan.clan-not-found');
      return self::RESULT_SUCCESS;
    }
    if (!(SteinsClan::$instance->hasPlayerRole($sender, SteinsClan::ROLE_GT_USER))) return self::RESULT_NO_RIGHTS;
    $player = SteinsPlayer::getPlayerByName(array_shift($args));
    if (!($player instanceof SteinsPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
    if ($player->isIgnorePlayer($sender)) return self::RESULT_PLAYER_IGNORES_YOU;
    if (SteinsClan::$instance->getPlayerClan($player) !== null) {
      $sender->sendLocalizedMessage('clan.cinvite-failed');
      return self::RESULT_SUCCESS;
    }
    SteinsClan::$instance->addClanRequest($player, $clanName);
    $player->sendLocalizedMessage('clan.cinvite-player', ['clan' => $clanName, 'player' => $sender->getCurrentName()]);
    $sender->sendLocalizedMessage('clan.cinvite-success', ['clan' => $clanName, 'player' => $player->getCurrentName()]);
    return self::RESULT_SUCCESS;
  }
}
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
use pocketmine\IPlayer;
use steinssquad\clan\SteinsClan;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class ClanDemoteCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('cdemote', 'clan', 'steinscore.clan.create');
    $this->registerOverload(['name' => 'player', 'type' => 'player']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (count($args) === 0) return self::RESULT_USAGE;
    if (($clanName = SteinsClan::$instance->getPlayerClan($sender)) === null) {
      $sender->sendLocalizedMessage('clan.clan-not-found');
      return self::RESULT_SUCCESS;
    }
    $player = SteinsPlayer::getOfflinePlayer(array_shift($args));
    if (!($player instanceof IPlayer) || $player === $sender) return self::RESULT_PLAYER_NOT_FOUND;
    if (SteinsClan::$instance->getPlayerClan($player) !== $clanName) {
      $sender->sendLocalizedMessage('clan.cdemote-failed');
      return self::RESULT_SUCCESS;
    }
    if (!(SteinsClan::$instance->hasPlayerRole($sender, SteinsClan::ROLE_OWNER))) return self::RESULT_NO_RIGHTS;
    $sender->sendLocalizedMessage('clan.cdemote-success', ['player' => SteinsPlayer::getPlayerName($player)]);
    SteinsClan::$instance->setPlayerRole($player, SteinsClan::ROLE_USER);
    return self::RESULT_SUCCESS;
  }
}
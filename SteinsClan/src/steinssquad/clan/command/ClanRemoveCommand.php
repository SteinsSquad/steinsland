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


class ClanRemoveCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('cremove', 'clan', 'steinscore.clan.create');
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (($clanName = SteinsClan::$instance->getPlayerClan($sender)) === null) {
      $sender->sendMessage($this->module('clan-not-found'));
      return self::RESULT_SUCCESS;
    }
    if (!(SteinsClan::$instance->hasPlayerRole($sender, SteinsClan::ROLE_OWNER))) return self::RESULT_NO_RIGHTS;
    SteinsClan::$instance->deleteClan($clanName);
    $sender->sendMessage($this->module('cremove-success', ['clan' => $clanName]));
    return self::RESULT_SUCCESS;
  }
}
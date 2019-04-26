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


class SkinCommand extends CustomCommand {

  private $oldSkins = [];

  public function __construct() {
    parent::__construct('skin', 'feature', 'steinscore.feature.skin');

    $this->registerOverload(['name' => 'player', 'type' => 'player', 'optional' => true]);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    $playerName = array_shift($args);
    if (!is_null($playerName) && is_null($player = SteinsPlayer::getPlayerByName($playerName))) return self::RESULT_PLAYER_NOT_FOUND;
    $skin = isset($player) ? $player->getSkin() : (isset($this->oldSkins[$sender->getLowerCaseName()]) ? $this->oldSkins[$sender->getLowerCaseName()] : null);
    if ($skin === null) return self::RESULT_USAGE;
    if (!isset($this->oldSkins[$sender->getLowerCaseName()])) {
      $this->oldSkins[$sender->getLowerCaseName()] = $sender->getSkin();
    }
    $sender->setSkin($skin);
    $sender->sendLocalizedMessage($playerName === null ? 'feature.skin-success-restore' : 'feature.skin-success', ['player' => !isset($player) ?: $player->getCurrentName()]);
    return self::RESULT_SUCCESS;
  }
}
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
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class ScreamCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('scream', 'feature', 'steinscore.feature.scream');
    $this->registerOverload(['name' => 'player', 'type' => 'player']);
  }

  public function onCommand(CommandSender $sender, array $args) {
    if (count($args) === 0) return self::RESULT_USAGE;
    $player = SteinsPlayer::getPlayerByName(array_shift($args));
    if (!($player instanceof SteinsPlayer)) return self::RESULT_PLAYER_NOT_FOUND;

    $player->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 60));
    $player->addTask(function (SteinsPlayer $player) {
      $pk = new LevelEventPacket();
      $pk->evid = LevelEventPacket::EVENT_GUARDIAN_CURSE;
      $pk->position = $player;
      $pk->data = 0;
      $player->dataPacket($pk);
    }, 2);

    $sender->sendMessage($this->module('scream-success', ['player' => $player->getCurrentName()]));
    return self::RESULT_SUCCESS;
  }
}
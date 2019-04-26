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

namespace steinssquad\feature\command\helpful;


use pocketmine\command\CommandSender;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class GamemodeCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('gamemode', 'feature', 'steinscore.feature.gamemode.use', ['gm']);
    $this->registerPermissibleOverload(
      ['steinscore.feature', 'steinscore.feature.gamemode'],
      ['name' => 'player', 'type' => 'player', 'optional' => true],
      ['name' => 'gamemode', 'type' => 'rawtext', 'enum' => ['name' => 'modes', 'values' => ['survival', 'creative', '1', '0']], 'optional' => true]
    );
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) === 0 && !($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    $target = $sender;
    if (count($args) > 0) {
      $target = SteinsPlayer::getPlayerByName(array_shift($args));
      if (!($target instanceof SteinsPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
      if ($sender instanceof SteinsPlayer && $target->getGroup()->getPriority() > $sender->getGroup()->getPriority()) return self::RESULT_PLAYER_HAS_IMMUNITY;
    }
    $gamemode = $target->isCreative() ? SteinsPlayer::SURVIVAL : SteinsPlayer::CREATIVE;
    if (count($args) > 0) {
      $gamemode = array_shift($args);
      if ($gamemode !== 'survival' && $gamemode !== 'creative' && $gamemode !== '1' && $gamemode !== '0') return self::RESULT_USAGE;
      $gamemode = $gamemode === 'survival' || $gamemode === '0' ? SteinsPlayer::SURVIVAL : SteinsPlayer::CREATIVE;
    }
    $target->setGamemode($gamemode);
    if ($target === $sender) {
      $sender->sendLocalizedMessage(
        $sender->isCreative() ? 'feature.gamemode-success-creative' : 'feature.gamemode-success-survival'
      );
    } else {
      $target->sendLocalizedMessage(
        $gamemode === SteinsPlayer::CREATIVE ? 'feature.gamemode-changed-creative' : 'feature.gamemode-changed-survival', ['player' => $sender instanceof SteinsPlayer? $sender->getCurrentName(): $sender->getName()]
      );
      $sender->sendMessage($this->translate(
        $target->isCreative() ? 'feature.gamemode-change-creative' : 'feature.gamemode-change-survival', ['player' => $target->getCurrentName()]
      ));
    }
    return self::RESULT_SUCCESS;
  }
}
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
use steinssquad\steinscore\utils\ParseUtils;


class FeedCommand extends CustomCommand {

  private $cooldown = [];

  public function __construct() {
    parent::__construct('feed', 'feature','steinscore.feature.feed.use.cd', ['satisfy', 'food']);
    $this->registerPermissibleOverload(
      [
        'steinscore.feature.feed'
      ], ['name' => 'player', 'type' => 'player', 'optional' => true]);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) === 0 and !$sender instanceof SteinsPlayer) return self::RESULT_IN_GAME;
    $player = $sender;
    if (count($args) > 0) {
      if (!$sender->hasPermission('steinscore.feature.food')) return self::RESULT_NO_RIGHTS;
      $player = SteinsPlayer::getPlayerByName(array_shift($args));
      if (!$player instanceof SteinsPlayer) return self::RESULT_PLAYER_NOT_FOUND;
    }
    if (!($sender->hasPermission('steinscore.feature.feed.use')) && $sender instanceof SteinsPlayer) {
      if (isset($this->cooldown[$sender->getLowerCaseName()]) && $this->cooldown[$sender->getLowerCaseName()] > time()) {
        $sender->sendLocalizedMessage('generic.cooldown', ['time' => ParseUtils::placeholderFromTimestamp(
          $this->cooldown[$sender->getLowerCaseName()] - time(), $sender
        )]);
        return self::RESULT_SUCCESS;
      }
      $this->cooldown[$sender->getLowerCaseName()] = time() + 300;
    }
    $player->setFood($player->getMaxFood());
    if ($sender !== $player) {
      $player->sendLocalizedMessage('feature.feed-you-satisfied', ['player' => $sender instanceof SteinsPlayer? $sender->getCurrentName(): $sender->getName()]);
      $sender->sendMessage($this->translate('feature.feed-satisfied-success', ['player' => $player->getCurrentName()]));
    } else $sender->sendLocalizedMessage('feature.feed-success');
    return self::RESULT_SUCCESS;
  }
}
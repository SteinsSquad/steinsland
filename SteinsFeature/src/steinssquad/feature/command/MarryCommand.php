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
use pocketmine\item\Item;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\level\sound\GhastShootSound;
use pocketmine\math\Vector3;
use pocketmine\Server;
use steinssquad\economy\SteinsEconomy;
use steinssquad\feature\SteinsFeature;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class MarryCommand extends CustomCommand {

  public const MARRY_PRICE = 10000;

  public function __construct() {
    parent::__construct('marry', 'feature', 'steinscore.feature.marry');

    $this->registerOverload(
      ['name' => 'action', 'type' => 'string', 'enum' => ['values' => ['invite', 'accept', 'divorce', 'kiss', 'sex'], 'name' => 'marriage']],
      ['name' => 'player', 'type' => 'player', 'optional' => 'true']
    );
  }

  public function onCommand(CommandSender $sender, array $args) {
    //invite, accept, divorce
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (count($args) === 0) return self::RESULT_USAGE;
    $action = array_shift($args);
    if ($action === 'invite') {
      if (count($args) === 0) return self::RESULT_USAGE;
      if (!($sender->hasMoney(self::MARRY_PRICE))) return self::RESULT_NOT_ENOUGH_MONEY;
      $player = SteinsPlayer::getPlayerByName(array_shift($args));
      if (!($player instanceof SteinsPlayer) || $player === $sender) return self::RESULT_PLAYER_NOT_FOUND;
      if ($player->isIgnorePlayer($sender)) return self::RESULT_PLAYER_IGNORES_YOU;
      if (SteinsFeature::$instance->isMarried($sender) || SteinsFeature::$instance->isMarried($player)) {
        $sender->sendLocalizedMessage('feature.marry-invite-failed');
        return self::RESULT_SUCCESS;
      }
      SteinsFeature::$instance->setMarryRequest($player, $sender);
      $sender->sendLocalizedMessage('feature.marry-invite-success', ['player' => $player->getCurrentName()]);
      $player->sendLocalizedMessage('feature.marry-invite-success-player', ['player' => $sender->getCurrentName()]);
      return self::RESULT_SUCCESS;
    } else if ($action === 'accept') {
      if (!(SteinsFeature::$instance->hasMarryRequest($sender))) {
        $sender->sendLocalizedMessage('feature.marry-accept-no-request');
        return self::RESULT_SUCCESS;
      }
      $player = SteinsPlayer::getOfflinePlayerExact(SteinsFeature::$instance->getMarryRequest($sender));
      if (SteinsFeature::$instance->isMarried($sender) || SteinsFeature::$instance->isMarried($player)) {
        $sender->sendLocalizedMessage('feature.marry-accept-failed');
        return self::RESULT_SUCCESS;//вряд-ли найдутся гении, но все-же
      }
      if (!(SteinsEconomy::$instance->hasMoney($player, self::MARRY_PRICE))) return self::RESULT_SUCCESS;
      SteinsFeature::$instance->marryPlayers($sender, $player);
      SteinsFeature::$instance->removeMarryRequest($sender);
      $sender->sendLocalizedMessage('feature.marry-accept-success', ['player' => SteinsPlayer::getPlayerName($player)]);
      if ($player instanceof SteinsPlayer) $player->sendLocalizedMessage('feature.marry-accept-success-player', ['player' => $sender->getCurrentName()]);
      return self::RESULT_SUCCESS;
    } else if ($action === 'divorce') {
      if (!(SteinsFeature::$instance->isMarried($sender))) {
        $sender->sendLocalizedMessage('feature.marry-not-married');
        return self::RESULT_SUCCESS;
      }
      SteinsFeature::$instance->divorcePlayer($sender);
      $sender->sendLocalizedMessage('feature.marry-divorce-success');
      return self::RESULT_SUCCESS;
    } else if ($action === 'kiss') {
      if (!(SteinsFeature::$instance->isMarried($sender))) {
        $sender->sendLocalizedMessage('feature.marry-not-married');
        return self::RESULT_SUCCESS;
      }
      $player = Server::getInstance()->getPlayerExact(SteinsFeature::$instance->getSpouse($sender));
      if (!($player instanceof SteinsPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
      if ($player->distance($sender) > 5) {
        $sender->sendLocalizedMessage('feature.marry-so-far');
        return self::RESULT_SUCCESS;
      }
      $particle = new HeartParticle($sender);
      for ($i = 0; $i <= $player->distance($sender); $i += 0.5) {
        $particle->x += ($player->x - $particle->x) / 10;
        $particle->y += ($player->y - $particle->y) / 10;
        $particle->z += ($player->z - $particle->z) / 10;
        $sender->getLevel()->addParticle($particle);
      }
      $sender->sendLocalizedMessage('feature.marry-kiss-success', ['player' => $player->getCurrentName()]);
      $player->sendLocalizedMessage('feature.marry-kiss-success-player', ['player' => $sender->getCurrentName()]);
      return self::RESULT_SUCCESS;
    } else if ($action === 'sex') {
      if (!(SteinsFeature::$instance->isMarried($sender))) {
        $sender->sendLocalizedMessage('feature.marry-not-married');
        return self::RESULT_SUCCESS;
      }
      $player = Server::getInstance()->getPlayerExact(SteinsFeature::$instance->getSpouse($sender));
      if (!($player instanceof SteinsPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
      if ($player->distance($sender) > 3) {
        $sender->sendLocalizedMessage('feature.marry-so-far');
        return self::RESULT_SUCCESS;
      }
      $sender->sendLocalizedMessage('feature.marry-sex-success');
      $player->sendLocalizedMessage('feature.marry-sex-success');
      $player->setImmobile();
      $sender->setImmobile();
      $task = function (SteinsPlayer $player) {
        $player->setImmobile(false);
        $player->sendLocalizedMessage('feature.marry-sex-finished');
        $particle = new SmokeParticle($player);
        for ($i = -5; $i < 5; $i++) {
          $particle->x = $player->x + $i;
          $particle->y = $player->y + $i;
          $particle->z = $player->z + $i;

          $player->getLevel()->addParticle($particle);
        }
        $player->getLevel()->addSound(new GhastShootSound($player), [$player]);
        if (mt_rand(0, 1000) === 0) {
          for ($i = 1; $i <= 10; $i++) $player->getLevel()->dropItem($player, Item::get(Item::DIAMOND, 0, 1), new Vector3(
            mt_rand(-30, 30) / 100, mt_rand(0, 10) / 10, mt_rand(-30, 30) / 100
          ));
        }
      };
      $player->setSneaking(true);
      $sender->setSneaking(true);
      $player->addTask($task, 3);
      $sender->addTask($task, 3);
      return self::RESULT_SUCCESS;
    }
    return self::RESULT_USAGE;
  }
}
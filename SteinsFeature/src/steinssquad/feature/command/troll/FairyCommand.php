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
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\Server;
use steinssquad\perms\model\Group;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\steinscore\utils\Percentage;


class FairyCommand extends CustomCommand {

  private $prizes;

  public function __construct() {
    parent::__construct('fairy', 'feature', 'steinscore.feature.fairy');

    $this->registerOverload(
      [
        'name' => 'arg',
        'type' => 'rawtext',
        'enum' => ['name' => 'args', 'values' => ['rtp', 'award']]
      ], [
        'name' => 'player',
        'type' => 'player',
        'optional' => true
      ]);

    $prizes = [];
    $prizes[] = ['chance' => 99, 'item' => [
      'name' => '%feature.fairy-prize-dirt',
      'callback' => function (SteinsPlayer $player) {
        $player->getInventory()->addItem(ItemFactory::get(
          Item::DIRT, 0, ($player->getInventory()->getSize() - count($player->getInventory()->getContents())) * 64));
      }
    ]];
    $prizes[] = ['chance' => 80, 'item' => [
      'name' => '%feature.fairy-prize-money',
      'callback' => function (SteinsPlayer $player) {
        $player->addMoney(mt_rand(1000, 3000));
      }
    ]];
    $prizes[] = ['chance' => 70, 'item' => [
      'name' => '%feature.fairy-prize-diamonds',
      'callback' => function (SteinsPlayer $player) {
        $player->getInventory()->addItem(ItemFactory::get(Item::DIAMOND, 0, mt_rand(1, 32)));
      }
    ]];
    $prizes[] = ['chance' => 50, 'item' => [
      'name' => '%feature.fairy-prize-item',
      'callback' => function (SteinsPlayer $player) {
        for ($attempts = 0; $attempts <= 20; $attempts++) {
          if (!ItemFactory::isRegistered($id = mt_rand(1, 513)) || array_search($id, [
            Item::BEDROCK,
            Item::TNT,
            Item::BUCKET,
            Item::FLINT_AND_STEEL,
            Item::SPAWN_EGG,
            Item::INVISIBLE_BEDROCK,
            Item::GOLDEN_APPLE,
            Item::LINGERING_POTION,
            Item::SPLASH_POTION,
            Item::ICE
          ]) !== false) continue;
          $item = ItemFactory::get($id);
          $item->setCount(mt_rand(1, $item->getMaxStackSize()));
          $player->getInventory()->addItem($item);
          break;
        }
      }
    ]];
    $prizes[] = ['chance' => 1, 'item' => [
      'name' => '%feature.fairy-prize-promotion',
      'callback' => function (SteinsPlayer $player) {
        $vip = Group::getGroup('vip');
        if (!$player->isGroupTemporary() && ($player->getGroup()->getPriority() < $vip->getPriority())) {
          $player->setGroup($vip, 604800);
        } else {
          $player->addMoney(10000);
          $player->sendLocalizedMessage('feature.fairy-promotion-failed');
        }
    }]];


    $this->prizes = new Percentage($prizes);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (count($args) === 0) return self::RESULT_USAGE;

    $action = array_shift($args);

    if ($action === 'rtp') {
      $players = Server::getInstance()->getOnlinePlayers();
      /** @var SteinsPlayer $player */
      $player = $players[array_rand($players)];
      $sender->teleport($player);
      $sender->sendLocalizedMessage('feature.fairy-teleport', ['player' => $player->getCurrentName()]);
    } else if ($action === 'award') {
      $player = array_shift($args);
      if (empty($player)) return self::RESULT_USAGE;
      $player = SteinsPlayer::getPlayerByName($player);
      if (!($player instanceof SteinsPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
      $prize = $this->prizes->nextRandom();
      $sender->sendLocalizedMessage('feature.fairy-award-success', ['player' => $player->getCurrentName(), 'prize' => $prize['name']]);
      $player->sendLocalizedMessage('feature.fairy-award-got', ['player' => $sender instanceof SteinsPlayer? $sender->getCurrentName(): $sender->getName(), 'prize' => $prize['name']]);
      $prize['callback']($player);
    } else return self::RESULT_USAGE;

    return self::RESULT_SUCCESS;
  }
}
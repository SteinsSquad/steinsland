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

namespace steinssquad\economy;


use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\IPlayer;
use pocketmine\item\ItemFactory;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\tile\Sign;
use steinssquad\economy\command\BalanceCommand;
use steinssquad\economy\command\BankCommand;
use steinssquad\economy\command\JobCommand;
use steinssquad\economy\command\LotteryCommand;
use steinssquad\economy\command\OrderBountyHunterCommand;
use steinssquad\economy\command\PayCommand;
use steinssquad\economy\command\SetMoneyCommand;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\steinscore\utils\GlobalSettings;
use steinssquad\steinscore\utils\Storage;
use steinssquad\steinscore\utils\Translator;


class SteinsEconomy extends PluginBase implements Listener {

  private const PERCENT = 0.09;

  /** @var SteinsEconomy */
  public static $instance;
  /** @var Storage */
  private $storage;

  private $orderedPlayers = [];

  public function onLoad() {
    Translator::registerLanguages('economy', $this->getFile() . 'resources/languages/');
  }

  public function onEnable() {
    self::$instance = $this;
    $this->saveResource('config.yml');

    $this->storage = new Storage($this->getDataFolder() . 'bank.db', ['money' => null, 'time' => null]);


    $commandMap = $this->getServer()->getCommandMap();
    $commandMap->register('steinscore/economy', new OrderBountyHunterCommand());

    $commandMap->register('steinscore/economy', new JobCommand());

    $commandMap->register('steinscore/economy', new LotteryCommand());

    $commandMap->register('steinscore/economy', new BankCommand());

    $commandMap->register('steinscore/economy', new BalanceCommand());
    $commandMap->register('steinscore/economy', new PayCommand());
    $commandMap->register('steinscore/economy', new SetMoneyCommand());

    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }

  public function onDisable() {
    $this->storage->save();
    $this->getConfig()->save();
  }

  /**
   * @priority MONITOR
   * @ignoreCancelled true
   *
   * @param BlockPlaceEvent $event
   * @return bool
   */
  public function handleBlockPlaceEvent(BlockPlaceEvent $event): bool {
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();
    if ($player->job === null || $player->isCreative()) return false;
    if (isset(GlobalSettings::get('jobs')[$player->job]['place'][$event->getBlock()->getId()])) {
      $player->addMoney(GlobalSettings::get('jobs')[$player->job]['place'][$event->getBlock()->getId()]);
    }
    return true;
  }

  /**
   * @priority MONITOR
   * @ignoreCancelled true
   *
   * @param BlockBreakEvent $event
   * @return bool
   */
  public function handleBlockBreakEvent(BlockBreakEvent $event): bool {
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();
    $block = $event->getBlock();
    if ($player->job === null || $player->isCreative()) return false;
    if (isset(GlobalSettings::get('jobs')[$player->job]['break'][$block->getId()])) {
      $player->addMoney(GlobalSettings::get('jobs')[$player->job]['break'][$block->getId()]);
    }
    if ($block->getId() === Block::BEDROCK && isset($this->getConfig()->get('shops', [])[$id = "$block->x:$block->y:$block->z"])) {
      $shops = $this->getConfig()->get('shops');
      unset($shops[$id]);
      $this->getConfig()->set("shops", $shops);
    }
    return true;
  }

  public function handlePlayerInteractEvent(PlayerInteractEvent $event): bool {
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();
    $block = $event->getBlock();
    if ($event->getAction() === $event::RIGHT_CLICK_BLOCK && isset($this->getConfig()->get('shops', [])["$block->x:$block->y:$block->z"])) {
      if ($player->isCreative()) return false;
      $event->setCancelled();
      $shopData = $this->getConfig()->get('shops')["$block->x:$block->y:$block->z"];

      $item = ItemFactory::fromString($shopData['item']);
      $item->setCount($shopData['count']);
      $placeholders = ['money' => $shopData['price'], 'count' => $item->getCount(), 'item' => $item->getName() . ' (' . $item->getId() . ':' . $item->getDamage() . ')'];
      if ($shopData['action'] === 'sell') {
        $count = $item->getCount();
        foreach ($player->getInventory()->getContents() as $i) {
          if ($item->equals($i)) {
            $count -= $i->getCount();
            if ($count <= 0) break;
          }
        }
        if ($count > 0) {
          $player->sendLocalizedMessage('economy.shop-sell-failed');
          return true;
        }
        $player->getInventory()->removeItem($item);
        $player->addMoney($shopData['price']);
        $player->sendLocalizedMessage('economy.shop-sell-success', $placeholders);
      } else {
        if (!($player->hasMoney($shopData['price']))) {
          $player->sendLocalizedMessage('generic.not-enough-money');
          return true;
        }
        if (!($player->getInventory()->canAddItem($item))) {
          $player->sendLocalizedMessage('economy.shop-buy-failed');
          return true;
        }
        $player->reduceMoney($shopData['price']);
        $player->getInventory()->addItem($item);
        $player->sendLocalizedMessage('economy.shop-buy-success', $placeholders);
      }
      return true;
    }
    if ($event->getBlock()->getId() === Block::SIGN_POST || $event->getBlock()->getId() === Block::WALL_SIGN) {
      $tile = $event->getBlock()->getLevel()->getTile($event->getBlock());
      if (!($tile instanceof Sign)) return false;
      if ($tile->getText()[0] !== 'buy' && $tile->getText()[0] !== 'sell') return false;
      if (!($player->hasPermission('steinscore.economy'))) return false;
      if (ItemFactory::fromString($tile->getText()[1]) === null) return false;
      $this->getConfig()->setNested("shops.$tile->x:$tile->y:$tile->z", [
        'action' => $tile->getText()[0],
        'item' => $tile->getText()[1],
        'count' => abs(intval($tile->getText()[2])),
        'price' => abs(intval($tile->getText()[3])),
      ]);
      return true;
    }

    return true;
  }

  public function bankBalance(IPlayer $player): ?int {
    if ($this->storage->exists(strtolower($player->getName()))) {
      list('money' => $money, 'time' => $timestamp) = $this->storage->findByID(strtolower($player->getName()));
      $time = intval((time() - $timestamp) / 3600);
      return $money + $time * intval($money * self::PERCENT);
    }
    return null;
  }

  public function bankDeposit(IPlayer $player, int $amount): bool {
    if (!($this->hasMoney($player, $amount))) return false;
    $this->reduceMoney($player, $amount);
    if ($this->storage->exists(strtolower($player->getName()))) return $this->storage->updateByID(strtolower($player->getName()), ['money' => $this->bankBalance($player) + $amount, 'time' => time(),]);
    return $this->storage->insert(strtolower($player->getName()), ['money' => $amount, 'time' => time()]);
  }

  public function bankWithdraw(IPlayer $player, int $amount): bool {
    if (($current = $this->bankBalance($player)) <= $amount) return false;
    $this->addMoney($player, $amount);
    if ($current === $amount) return $this->storage->deleteByID(strtolower($player->getName()));
    return $this->storage->updateByID(strtolower($player->getName()), ['money' => $current - $amount, 'time' => time()]);
  }

  public function orderPlayer(SteinsPlayer $player, SteinsPlayer $issuer, int $amount) {
    if ($issuer->hasMoney($amount)) {
      $issuer->reduceMoney($amount);
      if (!isset($this->orderedPlayers[$player->getLowerCaseName()])) {
        $this->orderedPlayers[$player->getLowerCaseName()] = 0;
      }
      $this->orderedPlayers[$player->getLowerCaseName()] += $amount;
      return true;
    }
    return false;
  }

  public function getHeadPrice(SteinsPlayer $player): ?int {
    return $this->orderedPlayers[$player->getLowerCaseName()] ?? null;
  }

  public function completeOrder(SteinsPlayer $player, SteinsPlayer $issuer): bool {
    if (isset($this->orderedPlayers[$player->getLowerCaseName()])) {
      $issuer->addMoney($this->getHeadPrice($player));
      unset($this->orderedPlayers[$player->getLowerCaseName()]);
      return true;
    }
    return false;
  }

  public function getMoney(IPlayer $player): ?int {
    if (is_null(SteinsPlayer::getOfflinePlayerExact($player->getName()))) return null;
    if ($player instanceof SteinsPlayer) return $player->getMoney();
    return Server::getInstance()->getOfflinePlayerData($player->getName())->getLong('money', 0);
  }

  public function hasMoney(IPlayer $player, int $amount): bool {
    return ($this->getMoney($player) ?? -1) >= $amount;
  }

  public function setMoney(IPlayer $player, int $amount): bool {
    if (is_null(SteinsPlayer::getOfflinePlayerExact($player->getName())) || $amount < 0) return false;
    $player instanceof SteinsPlayer ?
      $player->setMoney($amount) :
      $this->internalSetMoney($player, $amount);
    return true;
  }

  public function addMoney(IPlayer $player, int $amount): bool {
    if (is_null(SteinsPlayer::getOfflinePlayerExact($player->getName())) || $amount < 0) return false;
    $player instanceof SteinsPlayer ?
      $player->addMoney($amount) :
      $this->internalSetMoney($player, $this->getMoney($player) + $amount);
    return true;
  }

  public function reduceMoney(IPlayer $player, int $amount): bool {
    if (is_null(SteinsPlayer::getOfflinePlayerExact($player->getName())) || $amount < 0) return false;
    $currentAmount = $this->getMoney($player);
    if ($amount > $currentAmount) return false;
    $player instanceof SteinsPlayer ?
      $player->reduceMoney($amount) :
      $this->internalSetMoney($player, $currentAmount - $amount);
    return true;
  }

  public function payMoney(IPlayer $from, IPlayer $to, int $amount): bool {
    if (is_null(SteinsPlayer::getOfflinePlayerExact($from->getName())) || is_null(SteinsPlayer::getOfflinePlayerExact($to->getName()))) return false;
    if (!$this->hasMoney($from, $amount)) return false;
    if ($this->reduceMoney($from, $amount)) {
      return $this->addMoney($to, $amount);
    }
    return false;
  }

  private function internalSetMoney(IPlayer $player, int $amount) {
    $nbt = Server::getInstance()->getOfflinePlayerData($player->getName());
    $nbt->setLong('money', $amount);
    Server::getInstance()->saveOfflinePlayerData($player->getName(), $nbt);
  }
}
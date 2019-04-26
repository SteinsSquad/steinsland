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

namespace steinssquad\auth;


use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\IPlayer;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\Config;
use steinssquad\auth\command\ChangePasswordCommand;
use steinssquad\auth\command\LoginCommand;
use steinssquad\auth\command\RegisterCommand;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\steinscore\utils\Translator;


class SteinsAuth extends PluginBase implements Listener {

  /** @var SteinsAuth */
  public static $instance;

  private $auth_players = [];
  private $auth_attempts = [];

  private $newLogin = null;
  private $loginAttempts = 0;

  public function onLoad() {
    Translator::registerLanguages('auth', $this->getFile() . 'resources/languages/');
  }

  public function onEnable() {
    self::$instance = $this;
    if (!(is_dir($path = $this->getDataFolder() . 'players/'))) mkdir($path);

    $this->getServer()->getCommandMap()->register('steinscore/auth', new RegisterCommand());
    $this->getServer()->getCommandMap()->register('steinscore/auth', new LoginCommand());
    $this->getServer()->getCommandMap()->register('steinscore/auth', new ChangePasswordCommand());

    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }

  public function getPlayerData(IPlayer $player): ?array {
    if ($this->isPlayerRegistered($player)) {
      return (new Config($this->getDataFolder() . "players/" . strtolower($player->getName()) . ".json", Config::JSON))->getAll();
    }
    return null;
  }

  public function isPlayerRegistered(IPlayer $player): bool {
    return file_exists($this->getDataFolder() . "players/" . strtolower($player->getName()) . ".json");
  }

  public function isPlayerAuthenticated(SteinsPlayer $player): bool {
    return !(isset($this->auth_players[$player->getLowerCaseName()]));
  }

  public function registerPlayer(SteinsPlayer $player, string $password) {
    if ($this->isPlayerRegistered($player)) {
      $player->sendLocalizedMessage('auth.login-need');
      return false;
    }
    if (mb_strlen($password) < 6) {
      $player->sendLocalizedMessage('auth.register-short');
      return false;
    }

    $this->sessionUpdate($player, $password);

    unset($this->auth_players[strtolower($player->getName())]);
    $player->sendLocalizedMessage('auth.register-success', ['player' => $player->getCurrentName(), 'password' => $password]);
    foreach (SteinsPlayer::getOnlinePlayers() as $oPlayer) {
      if ($this->isPlayerAuthenticated($oPlayer)) {
        $oPlayer->showPlayer($player);
        $player->showPlayer($oPlayer);
      }
    }
    return true;
  }

  public function authenticatePlayer(SteinsPlayer $player, string $password): bool {
    if (!($this->isPlayerRegistered($player))) return false;
    if ($this->isPlayerAuthenticated($player)) {
      $player->sendLocalizedMessage('auth.already-logged-in');
      return false;
    }
    $data = $this->getPlayerData($player);
    if (!(password_verify($password, $data["password"]))) {
      if (!(isset($this->auth_attempts[$player->getLowerCaseName()]))) $this->auth_attempts[$player->getLowerCaseName()] = 0;
      $player->sendLocalizedMessage('auth.login-failed');
      if (++$this->auth_attempts[$player->getLowerCaseName()] >= 3) {
        $player->close("", $player->localize('auth.login-attempts'));
        unset($this->auth_attempts[$player->getLowerCaseName()]);
      }
      return false;
    }
    $this->sessionUpdate($player);
    unset($this->auth_players[$player->getLowerCaseName()]);
    $player->sendLocalizedMessage("auth.login-success");
    foreach (SteinsPlayer::getOnlinePlayers() as $oPlayer) {
      if ($this->isPlayerAuthenticated($oPlayer)) {
        $oPlayer->showPlayer($player);
        $player->showPlayer($oPlayer);
      }
    }
    return true;
  }

  public function changePlayerPassword(SteinsPlayer $player, string $password): bool {
    if (!($this->isPlayerRegistered($player))) return false;
    if (mb_strlen($password) < 6) {
      $player->sendLocalizedMessage('auth.register-short');
      return false;
    }
    $this->sessionUpdate($player, $password);
    $player->sendLocalizedMessage('auth.changepass-success', ['password' => $password]);
    return true;
  }

  public function sessionUpdate(SteinsPlayer $player, ?string $password = null) {
    $data = new Config($this->getDataFolder() . "players/" . $player->getLowerCaseName() . ".json", Config::JSON);
    if ($password !== null)  $data->set("password", password_hash($password, PASSWORD_DEFAULT));
    if ($data->get('firstip', null) === null) $data->set('firstip', $player->getAddress());
    $data->set("lastip", $player->getAddress());
    if ($data->get('firstlogin', null) === null) $data->set("firstlogin", time());
    $data->set("lastlogin", time());
    if ($data->set('firstclientid', null) === null) $data->set('firstclientid', $player->getUniqueId()->toString());
    $data->set("lastclientid", $player->getUniqueId()->toString());
    $data->save();
  }

  public function handlePlayerPreLoginEvent(PlayerPreLoginEvent $event) {
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();
    $players = 0;
    foreach ($this->getServer()->getOnlinePlayers() as $oPlayer) {
      if ($oPlayer === $player) continue;
      if (strtolower($oPlayer->getName()) == strtolower($player->getName())) {
        $event->setKickMessage($player->localize('auth.already-play'));
        $event->setCancelled(true);
        return false;
      } else if ($oPlayer->getAddress() === $player->getAddress() || $oPlayer->getUniqueId()->toString() === $player->getUniqueId()->toString()) {
        if (++$players > 3) {
          $event->setCancelled();
          $event->setKickMessage('С данного IP адреса онлайн слишком много человек.');
          return false;
        }
      }
    }
    $this->auth_players[$player->getLowerCaseName()] = true;
    return true;
  }

  public function handlePlayerJoinEvent(PlayerJoinEvent $event) {
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();

    if ($this->isPlayerRegistered($player)) {
      $data = $this->getPlayerData($player);
      if ((time() - $data['lastlogin']) <= 3600 && ($data['lastip'] === $player->getAddress() || $data['lastclientid'] === $player->getUniqueId()->toString())) {
        unset($this->auth_players[$player->getLowerCaseName()]);
        $player->sendLocalizedMessage('auth.login-success');
        foreach (SteinsPlayer::getOnlinePlayers() as $oPlayer) {
          if ($this->isPlayerAuthenticated($oPlayer)) {
            $oPlayer->showPlayer($player);
            $player->showPlayer($oPlayer);
          }
        }
      }
    } else {
      $player->sendLocalizedMessage('auth.first-message', ['player' => $player->getCurrentName()]);
    }

    $pk = new LevelEventPacket();
    $pk->evid = LevelEventPacket::EVENT_GUARDIAN_CURSE;
    $pk->position = $player;
    $pk->data = 0;
    $player->dataPacket($pk);

    foreach (SteinsPlayer::getOnlinePlayers() as $oPlayer) {
      if (!($this->isPlayerAuthenticated($oPlayer))) {
        $player->hidePlayer($oPlayer);
        $oPlayer->hidePlayer($player);
      }
      if (!($this->isPlayerAuthenticated($player))) {
        $player->hidePlayer($oPlayer);
        $oPlayer->hidePlayer($player);
      }
    }
  }


  public function handleDataEventReceiveEvent(DataPacketReceiveEvent $event) {
    $packet = $event->getPacket();
    if (!($packet instanceof LoginPacket)) return;
    if ($packet->clientId === 0) $event->setCancelled();
    if ($this->newLogin >= time()) {
      if (++$this->loginAttempts >= 10) {
        foreach (SteinsPlayer::getOnlinePlayers() as $player) {
          if ((time() - intval($player->creationTime)) > 5) continue;
          if (!($this->isPlayerAuthenticated($player))) $player->close();
        }
        $event->setCancelled();
      }
      return;
    }
    $this->newLogin = time() + 5;
    $this->loginAttempts = 0;
  }

  public function handlePlayerQuitEvent(PlayerQuitEvent $event) {
    unset($this->auth_players[$event->getPlayer()->getLowerCaseName()]);
    if (!($this->isPlayerRegistered($event->getPlayer()))) {
      $this->getScheduler()->scheduleDelayedTask(new class($event->getPlayer()->getLowerCaseName()) extends Task {

        private $player;

        public function __construct(string $player) {
          $this->player = $player;
        }

        public function onRun(int $currentTick) {
          if (file_exists($file = Server::getInstance()->getDataPath() . "players/$this->player.dat")) {
            unlink($file);
          }
        }
      }, 20 * 5);
    }
  }

  public function handlePlayerMoveEvent(PlayerMoveEvent $event) {
    if ($event->getFrom()->x === $event->getTo()->x && $event->getFrom()->y === $event->getTo()->y && $event->getFrom()->z === $event->getTo()->z) return;
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();
    if ($this->sendBlockMessage($player)) $event->setCancelled();
  }

  public function handlePlayerChatEvent(PlayerChatEvent $event) {
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();
    if ($this->sendBlockMessage($player)) {
      $event->setCancelled();
      return;
    }
    $recipients = $event->getRecipients();
    foreach ($recipients as $key => $recipient) {
      if ($recipient instanceof SteinsPlayer) {
        if (!($this->isPlayerAuthenticated($recipient))) unset($recipients[$key]);
      }
    }
    $event->setRecipients($recipients);
  }

  public function handlePlayerCommandPreprocessEvent(PlayerCommandPreprocessEvent $event) {
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();
    if (!($this->isPlayerAuthenticated($player))) {
      $args = explode(" ", strtolower($event->getMessage()));
      if (isset($args[0]) && ($args[0] === '/login' || $args[0] === '/register')) return;
      if ($this->sendBlockMessage($player)) $event->setCancelled();
    }
  }

  public function handlePlayerInteractEvent(PlayerInteractEvent $event) {
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();
    if ($this->sendBlockMessage($player)) $event->setCancelled();
  }

  public function handleBlockBreakEvent(BlockBreakEvent $event) {
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();
    if ($this->sendBlockMessage($player)) $event->setCancelled();
  }

  public function handleEntityDamageEvent(EntityDamageEvent $event) {
    $player = $event->getEntity();
    if ($player instanceof SteinsPlayer && $this->sendBlockMessage($player)) $event->setCancelled();
    if ($event instanceof EntityDamageByEntityEvent) {
      $attacker = $event->getDamager();
      if ($attacker instanceof SteinsPlayer && $this->sendBlockMessage($attacker)) $event->setCancelled();
    }
  }

  public function handlePlayerDropItemEvent(PlayerDropItemEvent $event) {
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();
    if ($this->sendBlockMessage($player)) $event->setCancelled();
  }

  public function handlePlayerItemConsumeEvent(PlayerItemConsumeEvent $event) {
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();
    if ($this->sendBlockMessage($player)) $event->setCancelled();
  }

  public function handlePlayerCraftItemEvent(CraftItemEvent $event) {
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();
    if ($this->sendBlockMessage($player)) $event->setCancelled();
  }

  public function sendBlockMessage(SteinsPlayer $player): bool {
    if ($this->isPlayerAuthenticated($player)) return false;
    $player->sendLocalizedTip($this->isPlayerRegistered($player) ? 'auth.login-need' : 'auth.register-need');
    return true;
  }
}
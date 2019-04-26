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

namespace steinssquad\admin;


use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\IPlayer;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use steinssquad\admin\command\ban\BanCommand;
use steinssquad\admin\command\ban\PardonCommand;
use steinssquad\admin\command\FreezeCommand;
use steinssquad\admin\command\jail\JailbreakCommand;
use steinssquad\admin\command\jail\JailCommand;
use steinssquad\admin\command\jail\JailQuitCommand;
use steinssquad\admin\command\jail\UnjailCommand;
use steinssquad\admin\command\KickCommand;
use steinssquad\admin\command\KillCommand;
use steinssquad\admin\command\mute\MuteCommand;
use steinssquad\admin\command\mute\UnmuteCommand;
use steinssquad\admin\command\NickCommand;
use steinssquad\admin\command\ReportCommand;
use steinssquad\admin\command\VanishCommand;
use steinssquad\admin\command\WarnCommand;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\steinscore\utils\GlobalSettings;
use steinssquad\steinscore\utils\ParseUtils;
use steinssquad\steinscore\utils\Storage;
use steinssquad\steinscore\utils\Translator;


class SteinsAdmin extends PluginBase implements Listener {

  /** @var SteinsAdmin */
  public static $instance;

  /** @var Storage */
  private $storage;

  private $muted = [];

  private $jailed = [];

  public function onLoad() {
    Translator::registerLanguages('admin', $this->getFile() . 'resources/languages/');
  }

  public function onEnable() {
    self::$instance = $this;

    $this->saveResource('config.yml');


    $this->storage = new Storage($this->getDataFolder() . 'bans.db', ['reason' => null, 'time' => null, 'admin' => null]);

    $commandMap = $this->getServer()->getCommandMap();

    foreach (['kick', 'kill', 'ban', 'pardon'] as $command) {
      $commandMap->unregister($commandMap->getCommand($command));
    }

    $commandMap->register('steinscore/admin/ban', new BanCommand());
    $commandMap->register('steinscore/admin/ban', new PardonCommand());

    $commandMap->register('steinscore/admin/jail', new JailbreakCommand());
    $commandMap->register('steinscore/admin/jail', new JailCommand());
    $commandMap->register('steinscore/admin/jail', new JailQuitCommand());
    $commandMap->register('steinscore/admin/jail', new UnjailCommand());

    $commandMap->register('steinscore/admin/mute', new MuteCommand());
    $commandMap->register('steinscore/admin/mute', new UnmuteCommand());

    $commandMap->register('steinscore/admin', new FreezeCommand());
    $commandMap->register('steinscore/admin', new KickCommand());
    $commandMap->register('steinscore/admin', new KillCommand());
    $commandMap->register('steinscore/admin', new NickCommand());
    $commandMap->register('steinscore/admin', new ReportCommand());
    $commandMap->register('steinscore/admin', new VanishCommand());
    $commandMap->register('steinscore/admin', new WarnCommand());

    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }

  public function onDisable() {
    $this->storage->save();
  }

  public function handlePlayerChatEvent(PlayerChatEvent $event) {
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();
    if (!($this->isMuted($player))) return true;
    $player->sendLocalizedMessage('admin.mute-chat-error');
    $event->setCancelled();
    return false;
  }

  public function handlePlayerPreLoginEvent(PlayerPreLoginEvent $event) {
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();

    if ($this->isBanned($player)) {
      if ($this->getBanTime($player) >= time()) {
        $event->setKickMessage($player->localize('admin.ban-you-banned', [
          'admin' => $this->getBanIssuer($player),
          'reason' => $this->getBanReason($player),
          'time' => $this->getBanTime($player) !== null ?
            ParseUtils::placeholderFromTimestamp($this->getBanTime($player), $player) : 'Forever'
        ]));
        $event->setCancelled(true);
        return false;
      }
      $this->pardonPlayer($player);
    }
    return true;
  }


  public function isBanned(IPlayer $player): bool {
    return $this->storage->exists(strtolower($player->getName()));
  }

  public function getBanTime(IPlayer $player): ?int {
    if ($this->isBanned($player)) {
      return $this->storage->findByID(strtolower($player->getName()))['time'];
    }
    return null;
  }

  public function getBanReason(IPlayer $player): ?string {
    if ($this->isBanned($player)) {
      return $this->storage->findByID(strtolower($player->getName()))['reason'];
    }
    return null;
  }

  public function getBanIssuer(IPlayer $player): ?string {
    if ($this->isBanned($player)) {
      return $this->storage->findByID(strtolower($player->getName()))['admin'];
    }
    return null;
  }

  public function banPlayer(IPlayer $player, string $reason = null, string $admin = null, int $timestamp = null): bool {
    return $this->storage->insert(strtolower($player->getName()), [
      'reason' => $reason,
      'admin' => $admin,
      'time' => $timestamp
    ]);
  }

  public function pardonPlayer(IPlayer $player) {
    $this->storage->deleteByID(strtolower($player->getName()));
  }

  public function isJailed(IPlayer $player): bool {
    return isset($this->jailed[strtolower($player->getName())]);
  }

  public function getJailTime(IPlayer $player): ?int {
    if (!$this->isJailed($player)) return null;
    return $this->jailed[strtolower($player->getName())]['time'];
  }

  public function getJailIssuer(IPlayer $player): ?string {
    if (!$this->isJailed($player)) return null;
    return $this->jailed[strtolower($player->getName())]['issuer'];
  }

  public function jailPlayer(IPlayer $player, IPlayer $issuer = null): bool {
    if ($this->isJailed($player)) return false;
    $this->jailed[strtolower($player->getName())] = ['time' => time() + 180, 'issuer' => is_null($issuer) ? null : $issuer->getName()];
    if ($player instanceof SteinsPlayer) $player->teleport(GlobalSettings::get('jail'));
    return true;
  }

  public function unjailPlayer(IPlayer $player): bool {
    if (!($this->isJailed($player))) return false;
    unset($this->jailed[strtolower($player->getName())]);
    if ($player instanceof SteinsPlayer) $player->teleport(Server::getInstance()->getDefaultLevel()->getSpawnLocation());
    return true;
  }

  public function isMuted(IPlayer $player): bool {
    return isset($this->muted[strtolower($player->getName())]);
  }

  public function mutePlayer(IPlayer $player, IPlayer $sender = null): bool {
    if ($this->isMuted($player)) return false;
    $this->muted[strtolower($player->getName())] = is_null($sender) ? null : $sender->getName();
    return true;
  }

  public function getMuteIssuer(IPlayer $player): ?string {
    if ($this->isMuted($player)) return $this->muted[strtolower($player->getName())];
    return null;
  }

  public function unmutePlayer(IPlayer $player): bool {
    if ($this->isMuted($player)) {
      unset($this->muted[strtolower($player->getName())]);
      return true;
    }
    return false;
  }
}
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

namespace steinssquad\teleport;


use pocketmine\IPlayer;
use pocketmine\level\Position;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\steinscore\utils\GlobalSettings;
use steinssquad\steinscore\utils\ParseUtils;
use steinssquad\steinscore\utils\Storage;
use steinssquad\steinscore\utils\Translator;
use steinssquad\teleport\command\BackCommand;
use steinssquad\teleport\command\home\DelHomeCommand;
use steinssquad\teleport\command\home\HomeCommand;
use steinssquad\teleport\command\home\HomesCommand;
use steinssquad\teleport\command\home\SetHomeCommand;
use steinssquad\teleport\command\RandomTeleportCommand;
use steinssquad\teleport\command\request\TeleportAcceptCommand;
use steinssquad\teleport\command\request\TeleportAskCommand;
use steinssquad\teleport\command\request\TeleportAskHereCommand;
use steinssquad\teleport\command\SpawnCommand;
use steinssquad\teleport\command\TeleportCommand;
use steinssquad\teleport\command\warp\DelWarpCommand;
use steinssquad\teleport\command\warp\SetWarpCommand;
use steinssquad\teleport\command\warp\WarpCommand;
use steinssquad\teleport\command\warp\WarpShortcutCommand;
use steinssquad\teleport\command\WildCommand;
use steinssquad\teleport\command\TopCommand;


class SteinsTeleport extends PluginBase {

  /** @var SteinsTeleport */
  public static $instance;

  public const TYPE_TPA = 0;
  public const TYPE_TPAHERE = 1;

  /** @var Storage */
  private $storage;
  private $teleportRequests = [];

  public function onLoad() {
    Translator::registerLanguages('teleport', $this->getFile() . 'resources/languages/');
  }

  public function onEnable() {
    self::$instance = $this;

    $commandMap = $this->getServer()->getCommandMap();
    $commandMap->unregister($commandMap->getCommand('tp'));

    $commandMap->register('steinscore/teleport/home', new DelHomeCommand());
    $commandMap->register('steinscore/teleport/home', new HomeCommand());
    $commandMap->register('steinscore/teleport/home', new HomesCommand());
    $commandMap->register('steinscore/teleport/home', new SetHomeCommand());

    $commandMap->register('steinscore/teleport/request', new TeleportAcceptCommand());
    $commandMap->register('steinscore/teleport/request', new TeleportAskCommand());
    $commandMap->register('steinscore/teleport/request', new TeleportAskHereCommand());

    $commandMap->register('steinscore/teleport/warp', new DelWarpCommand());
    $commandMap->register('steinscore/teleport/warp', new SetWarpCommand());
    $commandMap->register('steinscore/teleport/warp', new WarpCommand());

    $commandMap->register('steinscore/teleport', new BackCommand());
    $commandMap->register('steinscore/teleport', new RandomTeleportCommand());
    $commandMap->register('steinscore/teleport', new SpawnCommand());
    $commandMap->register('steinscore/teleport', new TeleportCommand());
    $commandMap->register('steinscore/teleport', new TopCommand());
    $commandMap->register('steinscore/teleport', new WildCommand());

    if (!is_dir($path = $this->getDataFolder())) mkdir($path);

    foreach (GlobalSettings::get('warps') as $warp => $pos) {
      $commandMap->register('steinscore/teleport/warp', new WarpShortcutCommand($warp, $pos));
    }

    $this->storage = new Storage($path . 'warps.db', [
      'shortcut' => false, 'position' => [], 'level' => null, 'owner' => null
    ]);

    foreach ($this->storage->find(function (array $data) {return $data['shortcut'] !== false;}) as $warpName => $warpData) {
      if (!($commandMap->getCommand($warpName))) $commandMap->register('steinscore/teleport/warp', new WarpShortcutCommand($warpName));
    }
  }

  public function onDisable() {
    $this->storage->save();
  }

  /**
   * @param IPlayer $player
   * @return Position[]
   */
  public function getHomes(IPlayer $player): array {
    if (is_null(SteinsPlayer::getOfflinePlayerExact($player->getName()))) return null;
    if ($player instanceof SteinsPlayer) return $player->getHomes();
    return ParseUtils::homesFromCompound(
      Server::getInstance()->getOfflinePlayerData($player->getName())->getCompoundTag('homes')
    );
  }

  public function getHome(IPlayer $player, string $name): ?Position {
    if (isset($this->getHomes($player)[strtolower($name)])) {
      return $this->getHomes($player)[strtolower($name)];
    }
    return null;
  }

  public function setHome(IPlayer $player, string $name, Position $position): bool {
    if (is_null(SteinsPlayer::getOfflinePlayerExact($player->getName()))) return false;
    if ($player instanceof SteinsPlayer) return $player->setHome($name, $position);

    $homes = $this->getHomes($player);
    $homes[strtolower($name)] = $position;
    $this->internalSetHomes($player, $homes);
    return true;
  }

  public function deleteHome(IPlayer $player, string $name): bool {
    if (is_null(SteinsPlayer::getOfflinePlayerExact($player->getName()))) return false;
    if ($player instanceof SteinsPlayer) return $player->deleteHome($name);
    $homes = $this->getHomes($player);
    if (!isset($homes[strtolower($name)])) {
      return false;
    }
    unset($homes[strtolower($name)]);
    $this->internalSetHomes($player, $homes);
    return true;
  }

  private function internalSetHomes(IPlayer $player, array $homes = []) {
    $nbt = Server::getInstance()->getOfflinePlayerData($player->getName());
    $nbt->setTag(ParseUtils::compoundFromHomes($homes));
    Server::getInstance()->saveOfflinePlayerData($player->getName(), $nbt);
  }

  public function hasTeleportRequest(SteinsPlayer $player): bool {
    return isset($this->teleportRequests[$player->getLowerCaseName()]);
  }

  public function getTeleportIssuer(SteinsPlayer $player): ?string {
    if ($this->hasTeleportRequest($player)) {
      return $this->teleportRequests[$player->getLowerCaseName()]['issuer'];
    }
    return null;
  }

  public function getTeleportType(SteinsPlayer $player): ?int {
    if ($this->hasTeleportRequest($player)) {
      return $this->teleportRequests[$player->getLowerCaseName()]['type'];
    }
    return null;
  }

  public function removeTeleportRequest(SteinsPlayer $player) {
    if ($this->hasTeleportRequest($player)) {
      unset($this->teleportRequests[$player->getLowerCaseName()]);
    }
  }

  public function addTeleportRequest(SteinsPlayer $player, SteinsPlayer $target, int $type = self::TYPE_TPA) {
    $this->teleportRequests[$target->getLowerCaseName()] = [
      'issuer' => $player->getLowerCaseName(),
      'type' => $type
    ];
  }


  public function getWarps() {
    return $this->storage->find();
  }

  public function getWarpCount(IPlayer $player) {
    return $this->storage->count(function (array $data) use ($player) {return $data['owner'] === strtolower($player->getName());});
  }

  public function warpExists(string $warpName) {
    return $this->storage->exists(strtolower($warpName));
  }

  public function getWarp(string $warpName): ?array {
    if ($this->warpExists($warpName)) {
      return $this->storage->findByID(strtolower($warpName));
    }
    return null;
  }

  public function getWarpPosition(string $warpName): ?Position {
    if ($this->warpExists($warpName)) {
      $position = new Position(...$this->getWarp($warpName)['position']);
      $position->setLevel(Server::getInstance()->getDefaultLevel());
      if ($this->getWarp($warpName)['level'] !== null) {
        $position->setLevel(
          Server::getInstance()->getLevelByName($this->getWarp($warpName)['level']) ?? Server::getInstance()->getDefaultLevel()
        );
      }
      return $position;
    }
    return null;
  }

  public function setWarp(string $warpName, Position $position, bool $shortcut = false, IPlayer $owner = null) {
    $data = [
      'position' => [$position->getFloorX(), $position->getFloorY(), $position->getFloorZ()],
      'level' => ($position->getLevel() ?? Server::getInstance()->getDefaultLevel())->getFolderName(),
      'owner' => $owner !== null ? $owner->getName() : null,
      'shortcut' => $shortcut
    ];
    if ($this->storage->exists($warpName = strtolower($warpName))) {
      $this->storage->updateByID($warpName, $data);
    } else {
      $this->storage->insert($warpName, $data);
      if ($shortcut && Server::getInstance()->getCommandMap()->getCommand($warpName) === null) {
        Server::getInstance()->getCommandMap()->register('steinscore', new WarpShortcutCommand($warpName));
      }
    }
  }

  public function deleteWarp(string $warpName): bool {
    if (!$this->warpExists($warpName)) return false;
    if ($this->getWarp($warpName)['shortcut']) {
      Server::getInstance()->getCommandMap()->unregister(Server::getInstance()->getCommandMap()->getCommand($warpName));
    }
    $this->storage->deleteByID(strtolower($warpName));
    return true;
  }

  public function getBackPosition(IPlayer $player): ?Position {
    if (is_null(SteinsPlayer::getOfflinePlayerExact($player->getName()))) return null;
    if ($player instanceof SteinsPlayer) return $player->getBackPosition();
    return ParseUtils::backPositionFromCompound(
      Server::getInstance()->getOfflinePlayerData($player->getName())->getCompoundTag('backPosition')
    );
  }

  public function setBackPosition(IPlayer $player, Position $position): bool {
    if (is_null(SteinsPlayer::getOfflinePlayerExact($player->getName()))) return null;
    if ($player instanceof SteinsPlayer) return $player->setBackPosition($position);
    $nbt = Server::getInstance()->getOfflinePlayerData($player->getName());
    $nbt->setTag(ParseUtils::compoundFromBackPosition($position));
    Server::getInstance()->saveOfflinePlayerData($player->getName(), $nbt);
    return true;
  }
}
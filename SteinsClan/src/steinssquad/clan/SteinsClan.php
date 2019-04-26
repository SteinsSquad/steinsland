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

namespace steinssquad\clan;


use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\IPlayer;
use pocketmine\level\Position;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use steinssquad\clan\command\ClanAcceptCommand;
use steinssquad\clan\command\ClanCreateCommand;
use steinssquad\clan\command\ClanDemoteCommand;
use steinssquad\clan\command\ClanDepositCommand;
use steinssquad\clan\command\ClanHomeCommand;
use steinssquad\clan\command\ClanInviteCommand;
use steinssquad\clan\command\ClanKickCommand;
use steinssquad\clan\command\ClanLeaveCommand;
use steinssquad\clan\command\ClanPromoteCommand;
use steinssquad\clan\command\ClanRemoveCommand;
use steinssquad\clan\command\ClanSetHomeCommand;
use steinssquad\clan\command\ClanTopCommand;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\steinscore\utils\Storage;
use steinssquad\steinscore\utils\Translator;


class SteinsClan extends PluginBase implements Listener {

  public const ROLE_MEMBER = 0;
  public const ROLE_USER = 1;
  public const ROLE_OFFICER = 2;
  public const ROLE_OWNER = 3;

  public const ROLE_GT_USER = 4;
  public const ROLE_LT_OWNER = 5;
  public const ROLE_NOT_MEMBER = 6;

  /** @var SteinsClan */
  public static $instance;

  private $requests = [];

  /** @var Storage */
  private $storage;

  public function onLoad() {
    Translator::registerLanguages('clan', $this->getFile() . 'resources/languages/');
  }

  public function onEnable() {
    self::$instance = $this;

    $this->storage = new Storage($this->getDataFolder() . 'clans.db', [
      'exp' => 0,
      'money' => 0,
      'home' => [null, null, null, null],
      'members' => []
    ]);

    $commandMap = $this->getServer()->getCommandMap();
    $commandMap->register('steinscore/clan', new ClanCreateCommand());
    $commandMap->register('steinscore/clan', new ClanInviteCommand());
    $commandMap->register('steinscore/clan', new ClanAcceptCommand());
    $commandMap->register('steinscore/clan', new ClanDepositCommand());
    $commandMap->register('steinscore/clan', new ClanSetHomeCommand());
    $commandMap->register('steinscore/clan', new ClanHomeCommand());
    $commandMap->register('steinscore/clan', new ClanKickCommand());
    $commandMap->register('steinscore/clan', new ClanLeaveCommand());
    $commandMap->register('steinscore/clan', new ClanPromoteCommand());
    $commandMap->register('steinscore/clan', new ClanDemoteCommand());
    //$commandMap->register('steinscore/clan', new ClanTopCommand());TODO
    $commandMap->register('steinscore/clan', new ClanRemoveCommand());

    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }

  public function onDisable() {
    $this->storage->save();
  }

  public function handleEntityDamageEvent(EntityDamageEvent $event): bool {
    if (!($event instanceof EntityDamageByEntityEvent)) return false;
    $attacker = $event->getDamager();
    $victim = $event->getEntity();
    if (!($victim instanceof SteinsPlayer) || !($attacker instanceof SteinsPlayer)) return false;
    if ($this->getPlayerClan($attacker) === $this->getPlayerClan($victim) && $this->getPlayerClan($attacker) !== null) {
      $event->setCancelled();
    }
    return true;
  }

  public function hasClanRequest(IPlayer $player): bool {
    return isset($this->requests[strtolower($player->getName())]);
  }

  public function getClanRequest(IPlayer $player): ?string {
    return $this->requests[strtolower($player->getName())] ?? null;
  }

  public function addClanRequest(IPlayer $player, string $clanName) {
    $this->requests[strtolower($player->getName())] = $clanName;
  }

  public function removeClanRequest(IPlayer $player) {
    if ($this->hasClanRequest($player)) unset($this->requests[strtolower($player->getName())]);
  }

  public function clanExists(string $clanName): bool {
    return $this->storage->exists(strtolower($clanName));
  }

  public function createClan(string $clanName, IPlayer $creator): bool {
    if ($this->clanExists($clanName)) return false;
    return $this->storage->insert(strtolower($clanName), [
      'members' => [strtolower($creator->getName()) => self::ROLE_OWNER]
    ]);
  }

  public function deleteClan(string $clanName): bool {
    if (!($this->clanExists($clanName))) return false;
    return $this->storage->deleteByID(strtolower($clanName));
  }

  public function getExp(string $clanName): ?int {
    if (!($this->clanExists($clanName))) return null;
    return $this->storage->findByID(strtolower($clanName))['exp'];
  }

  public function getLevel(string $clanName):? int {
    if (!($this->clanExists($clanName))) return null;
    return intval($this->getExp($clanName) / 1000);
  }

  public function addExp(string $clanName, int $exp): bool {
    if (!($this->clanExists($clanName))) return false;
    return $this->storage->updateByID(strtolower($clanName), ['exp' => $this->getExp($clanName) + $exp]);
  }

  public function getMoney(string $clanName): ?int {
    if (!($this->clanExists($clanName))) return null;
    return $this->storage->findByID(strtolower($clanName))['money'];
  }

  public function reduceMoney(string $clanName, int $amount): bool {
    if (!($this->clanExists($clanName))) return false;
    if ($this->getMoney($clanName) < $amount) return false;
    return $this->storage->updateByID(strtolower($clanName), ['exp' => $this->getExp($clanName) - $amount]);
  }

  public function addMoney(string $clanName, int $amount): bool {
    if (!($this->clanExists($clanName))) return false;
    return $this->storage->updateByID(strtolower($clanName), ['money' => $this->getExp($clanName) + $amount]);
  }

  public function getHome(string $clanName): ?Position {
    if (!($this->clanExists($clanName))) return null;
    if ($this->storage->findByID(strtolower($clanName))['home'][0] === null) return null;
    return (new Position())
      ->setComponents(...$this->storage->findByID(strtolower($clanName))['home'])
      ->setLevel(Server::getInstance()->getLevelByName($this->storage->findByID(strtolower($clanName))['home'][3]));
  }

  public function setHome(string $clanName, Position $position): bool {
    if (!($this->clanExists($clanName))) return false;
    return $this->storage->updateByID(strtolower($clanName), ['home' => [
      $position->getX(), $position->getY(), $position->getZ(), $position->getLevel()->getName()
    ]]);
  }

  public function getMembers(string $clanName): ?array {
    if (!($this->clanExists($clanName))) return null;
    return $this->storage->findByID(strtolower($clanName))['members'];
  }

  public function getPlayerClan(IPlayer $player): ?string {
    return $this->storage->findID(function(array $data) use ($player) {
      return isset($data['members'][strtolower($player->getName())]);
    });
  }

  public function getPlayerRole(IPlayer $player): ?int {
    if (($clan = $this->getPlayerClan($player)) === null) return null;
    return $this->storage->findByID($clan)['members'][strtolower($player->getName())];
  }

  public function hasPlayerRole(IPlayer $player, int $role = self::ROLE_MEMBER): bool {
    if (($currentRole = $this->getPlayerRole($player)) === null) return $role === self::ROLE_NOT_MEMBER;
    if ($role === self::ROLE_MEMBER) return true;
    return $role < self::ROLE_GT_USER ? $currentRole === $role : ($role === self::ROLE_GT_USER ? $currentRole > self::ROLE_USER : $currentRole < self::ROLE_OWNER);
  }

  public function setPlayerRole(IPlayer $player, int $role = self::ROLE_OFFICER): bool {
    if (($clan = $this->getPlayerClan($player)) === null) return false;
    $members = $this->getMembers($clan);
    $members[strtolower($player->getName())] = $role;
    return $this->storage->updateByID($clan, ['members' => $members]);
  }

  public function addMember(string $clanName, IPlayer $player, int $role = self::ROLE_USER): bool {
    if (!($this->clanExists($clanName))) return false;
    if ($this->getPlayerClan($player) !== null) return false;
    $members = $this->getMembers($clanName);
    $members[strtolower($player->getName())] = $role;
    return $this->storage->updateByID(strtolower($clanName), ['members' => $members]);
  }

  public function removeMember(IPlayer $player) {
    if (($clanName = $this->getPlayerClan($player)) === null) return false;
    $members = $this->getMembers($clanName);
    unset($members[strtolower($player->getName())]);
    return $this->storage->updateByID($clanName, ['members' => $members]);
  }

  public function getClanTop(int $limit = -1, int $offset = 0) {
    return $this->storage->findSorted(function (array $a, array $b): int {
      $exp = $a['exp'] - $b['exp'];
      if ($exp < 0) return $exp;
      return $a['money'] - $b['money'];
    }, $limit, $offset);
  }
}
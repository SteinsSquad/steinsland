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

namespace steinssquad\region;


use pocketmine\block\Chest;
use pocketmine\block\Door;
use pocketmine\block\Furnace;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\IPlayer;
use pocketmine\level\Position;
use pocketmine\math\Math;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use steinssquad\region\command\AddAdminCommand;
use steinssquad\region\command\AddMemberCommand;
use steinssquad\region\command\BuyRegion;
use steinssquad\region\command\ClaimCommand;
use steinssquad\region\command\DelMemberCommand;
use steinssquad\region\command\FlagCommand;
use steinssquad\region\command\InfoCommand;
use steinssquad\region\command\LeaveCommand;
use steinssquad\region\command\PosCommand;
use steinssquad\region\command\UnclaimCommand;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\steinscore\utils\Storage;
use steinssquad\steinscore\utils\Translator;


class SteinsRegion extends PluginBase implements Listener {

  public const FLAGS = [
    'starve' => true,
    'breathe' => true,
    'keep-inventory' => false,
    'build' => false,
    'pvp' => false,
    'unfallable' => false,
    'unburned' => false,
    'chat' => true,
    'priority' => false,
    'use' => false,
    'price' => -1,
    'greeting' => '',
  ];

  public const PERMISSION_MEMBER = 0;
  public const PERMISSION_USER = 1;
  public const PERMISSION_ADMIN = 2;
  public const PERMISSION_OWNER = 3;

  public const PERMISSION_GT_USER = 4;
  public const PERMISSION_LT_OWNER = 5;
  public const PERMISSION_NOT_MEMBER = 6;

  /** @var SteinsRegion */
  public static $instance;

  /** @var Storage */
  private $storage;

  private $selections = [];

  public function onLoad() {
    Translator::registerLanguages('region', $this->getFile() . 'resources/languages/');
  }

  public function onEnable() {
    self::$instance = $this;
    $this->storage = new Storage($this->getDataFolder() . 'regions.db', [
      'min' => [null, null],
      'max' => [null, null],
      'level' => null,
      'flags' => self::FLAGS,
      'members' => []
    ]);

    $commandMap = $this->getServer()->getCommandMap();
    $commandMap->register('steinscore/region', new PosCommand(1));
    $commandMap->register('steinscore/region', new PosCommand(2));
    $commandMap->register('steinscore/region', new ClaimCommand());
    $commandMap->register('steinscore/region', new UnclaimCommand());
    $commandMap->register('steinscore/region', new InfoCommand());
    $commandMap->register('steinscore/region', new AddMemberCommand());
    $commandMap->register('steinscore/region', new DelMemberCommand());
    $commandMap->register('steinscore/region', new AddAdminCommand());
    $commandMap->register('steinscore/region', new FlagCommand());
    $commandMap->register('steinscore/region', new LeaveCommand());
    $commandMap->register('steinscore/region', new BuyRegion());

    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }

  public function onDisable() {
    $this->storage->save();
  }

  /**
   * @priority LOWEST
   * @ignoreCancelled true
   *
   * @param BlockPlaceEvent $event
   * @return bool
   */
  public function handleBlockPlaceEvent(BlockPlaceEvent $event): bool {
    if ($this->getFlagInside($event->getBlock(), 'build', true) === false && !$event->getPlayer()->hasPermission('steinscore.region')) {
      foreach ($this->getRegionsInside($event->getBlock()) as $region) {
        if ($this->hasRegionPermission($region, $event->getPlayer(), self::PERMISSION_NOT_MEMBER)) {
          $event->setCancelled();
          return false;
        }
      }
    }
    return true;
  }

  /**
   * @priority LOWEST
   * @ignoreCancelled true
   *
   * @param BlockBreakEvent $event
   * @return bool
   */
  public function handleBlockBreakEvent(BlockBreakEvent $event): bool {
    if ($this->getFlagInside($event->getBlock(), 'build', true) === false && !$event->getPlayer()->hasPermission('steinscore.region')) {
      foreach ($this->getRegionsInside($event->getBlock()) as $region) {
        if ($this->hasRegionPermission($region, $event->getPlayer(), self::PERMISSION_NOT_MEMBER)) {
          $event->setCancelled();
          return false;
        }
      }
    }
    return true;
  }

  /**
   * @priority LOWEST
   *
   * @param PlayerDeathEvent $event
   * @return bool
   */
  public function handlePlayerDeathEvent(PlayerDeathEvent $event): bool {
    if ($this->getFlagInside($event->getPlayer(), 'keep-inventory', false) === true) {
      $event->setKeepInventory(true);
    }
    return true;
  }

  /**
   * @priority LOWEST
   * @ignoreCancelled true
   *
   * @param PlayerChatEvent $event
   * @return bool
   */
  public function handlePlayerChatEvent(PlayerChatEvent $event): bool {
    if ($this->getFlagInside($event->getPlayer(), 'chat', true) || $event->getPlayer()->hasPermission('steinscore.region')) return true;
    foreach ($this->getRegionsInside($event->getPlayer()) as $region) {
      if ($this->hasRegionPermission($region, $event->getPlayer(), self::PERMISSION_NOT_MEMBER)) {
        $event->setCancelled();
        break;
      }
    }
    return true;
  }

  /**
   * @priority LOWEST
   * @ignoreCancelled true
   *
   * @param EntityDamageEvent $event
   * @return bool
   */
  public function handleEntityDamageEvent(EntityDamageEvent $event): bool {
    $victim = $event->getEntity();
    if (!($victim instanceof SteinsPlayer)) return false;
    if ($event instanceof EntityDamageByEntityEvent) {
      $attacker = $event->getDamager();
      if (!($attacker instanceof SteinsPlayer) || $attacker->hasPermission('steinscore.region')) return false;
      $event->setCancelled((
        $this->getFlagInside($victim, 'pvp', true) === false ||
        $this->getFlagInside($attacker, 'pvp', true) === false
      ));
    }
    if ($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
      $event->setCancelled($this->getFlagInside($victim, 'unfallable', false) === true);
    } else if (
      $event->getCause() === EntityDamageEvent::CAUSE_FIRE ||
      $event->getCause() === EntityDamageEvent::CAUSE_FIRE_TICK ||
      $event->getCause() === EntityDamageEvent::CAUSE_LAVA
    ) {
      $event->setCancelled($this->getFlagInside($victim, 'unburned', false) === true);
    }
    return true;
  }

  /**
   * @priority LOWEST
   * @ignoreCancelled true
   *
   * @param PlayerInteractEvent $event
   * @return bool
   */
  public function handlePlayerInteractEvent(PlayerInteractEvent $event): bool {
    $block = $event->getBlock();
    if (!($block instanceof Chest || $block instanceof Door || $block instanceof Furnace)) return false;
    if ($this->getFlagInside($block, 'use', true) === false && !$event->getPlayer()->hasPermission('steinscore.region')) {
      foreach ($this->getRegionsInside($event->getBlock()) as $region) {
        if ($this->hasRegionPermission($region, $event->getPlayer(), self::PERMISSION_NOT_MEMBER)) {
          $event->setCancelled();
          return false;
        }
      }
    }
    return true;
  }

  /**
   * @priority LOWEST
   * @ignoreCancelled true
   *
   * @param PlayerMoveEvent $event
   * @return bool
   */
  public function handlePlayerMoveEvent(PlayerMoveEvent $event): bool {
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();

    if (count($regions = $this->getRegionsInside($event->getTo())) > 0) {
      foreach ($regions as $region) {
        if ($this->getRegionFlag($region, 'greeting') === '') continue;
        if (array_search($region, $this->getRegionsInside($event->getFrom())) !== false) continue;
        $player->sendMessage(TextFormat::colorize(str_replace('%player', $player->getCurrentName(), $this->getRegionFlag($region, 'greeting'))));
      }
      return true;
    }
    return false;
  }

  /**
   * @param IPlayer $player
   * @return Position[]
   */
  public function getSelections(IPlayer $player): array {
    return $this->selections[strtolower($player->getName())] ?? [];
  }

  public function setSelection(IPlayer $player, int $id, Position $position) {
    $this->selections[strtolower($player->getName())][$id] = $position->setComponents(Math::ceilFloat($position->x), 0, Math::ceilFloat($position->z));
  }

  public function removeSelections(IPlayer $player) {
    unset($this->selections[strtolower($player->getName())]);
  }

  public function getRegionsInside(Position $position): array {
    return array_keys($this->storage->find(function (array $data) use ($position) {
      return $data['level'] === $position->getLevel()->getFolderName() &&
        $data['min'][0] <= Math::ceilFloat($position->x) && Math::ceilFloat($position->x) <= $data['max'][0] &&
        $data['min'][1] <= Math::ceilFloat($position->z) && Math::ceilFloat($position->z) <= $data['max'][1];
    }));
  }

  public function getRegionsBetween(Position $min, Position $max): array {
    return array_keys($this->storage->find(function (array $data) use ($min, $max) {
      return $data['level'] === $min->getLevel()->getFolderName() &&
        $data['min'][0] <= max($max->getX(), $min->getX()) && min($min->getX(), $max->getX()) <= $data['max'][0] &&
        $data['min'][1] <= max($max->getZ(), $min->getZ()) && min($min->getZ(), $max->getZ()) <= $data['max'][1];
    }));
  }

  public function getPlayerRegions(IPlayer $player, int $permission = self::PERMISSION_MEMBER): array {
    return array_keys($this->storage->find(function(array $data) use ($player, $permission) {
      if ($permission === self::PERMISSION_NOT_MEMBER) return !isset($data['members'][strtolower($player->getName())]);
      if ($permission === self::PERMISSION_MEMBER) return isset($data['members'][strtolower($player->getName())]);
      if (!(isset($data['members'][strtolower($player->getName())]))) return false;

      $playerPermission = $data['members'][strtolower($player->getName())];

      return $permission <= self::PERMISSION_OWNER ?
        $playerPermission === $permission : (
          $permission === self::PERMISSION_GT_USER ?
            $playerPermission > self::PERMISSION_USER :
            $playerPermission < self::PERMISSION_OWNER
        );
    }));
  }

  public function regionExists(string $regionName) : bool {
    return $this->storage->findByID(strtolower($regionName)) !== null;
  }

  public function claimRegion(string $regionName, Position $min, Position $max, IPlayer $player): bool {
    return $this->storage->insert(strtolower($regionName), $data = [
      'min' => [min($min->getX(), $max->getX()), min($min->getZ(), $max->getZ())],
      'max' => [max($min->getX(), $max->getX()), max($min->getZ(), $max->getZ())],
      'level' => $min->getLevel()->getFolderName(),
      'members' => [strtolower($player->getName()) => self::PERMISSION_OWNER],
      'flags' => self::FLAGS
    ]);
  }

  public function unclaimRegion(string $regionName): bool {
    return $this->storage->deleteByID(strtolower($regionName));
  }

  public function getRegionMin(string $regionName): ?array {
    if (!($this->regionExists($regionName))) return null;
    return $this->storage->findByID(strtolower($regionName))['min'];
  }

  public function getRegionMax(string $regionName): ?array {
    if (!($this->regionExists($regionName))) return null;
    return $this->storage->findByID(strtolower($regionName))['max'];
  }

  public function getRegionLevel(string $regionName): ?string{
    if (!($this->regionExists($regionName))) return null;
    return $this->storage->findByID(strtolower($regionName))['level'] ?? null;
  }

  public function getRegionFlags(string $regionName): ?array {
    if (!($this->regionExists($regionName))) return null;
    return $this->storage->findByID(strtolower($regionName))['flags'];
  }

  public function getRegionFlag(string $regionName, string $flag) {
    if (!($this->regionExists($regionName))) return null;
    return $this->getRegionFlags($regionName)[strtolower($flag)] ?? null;
  }

  public function setRegionFlag(string $regionName, string $flag, $value): bool {
    if (!($this->regionExists($regionName))) return false;
    $flags = $this->getRegionFlags($regionName);
    $flags[strtolower($flag)] = $value;
    return $this->storage->updateByID(strtolower($regionName), ['flags' => $flags]);
  }

  public function getFlagInside(Position $position, string $flagName, $default = null) {
    foreach (($regions = $this->getRegionsInside($position)) as $region) {
      $default = $this->getRegionFlag($region, $flagName);
      if ($this->getRegionFlag($region, 'priority') === true) break;
    }
    return $default;
  }

  public function getRegionMembers(string $regionName): array {
    if (!($this->regionExists($regionName))) return null;
    return $this->storage->findByID(strtolower($regionName))['members'];
  }

  public function hasRegionPermission(string $regionName, IPlayer $player, int $permission = self::PERMISSION_MEMBER): bool {
    if (!($this->regionExists($regionName))) return false;
    $playerPermission = $this->getRegionMembers($regionName)[strtolower($player->getName())] ?? null;
    if ($playerPermission === null) return $permission === self::PERMISSION_NOT_MEMBER;
    return $permission !== self::PERMISSION_MEMBER ? ($permission <= self::PERMISSION_OWNER ?
      $playerPermission === $permission : (
      $permission === self::PERMISSION_GT_USER ?
        $playerPermission > self::PERMISSION_USER :
        $playerPermission < self::PERMISSION_OWNER
      )) : true;
  }

  public function addRegionMember(string $regionName, IPlayer $player, int $permission = self::PERMISSION_USER): bool {
    if (!($this->regionExists($regionName)) || $this->hasRegionPermission($regionName, $player)) return false;
    $members = $this->getRegionMembers($regionName);
    $members[strtolower($player->getName())] = $permission;
    return $this->storage->updateByID(strtolower($regionName), ['members' => $members]);
  }

  public function setRegionPermission(string $regionName, IPlayer $player, int $permission = self::PERMISSION_ADMIN): bool {
    if (!($this->regionExists($regionName)) || $this->hasRegionPermission($regionName, $player, self::PERMISSION_NOT_MEMBER)) return false;
    $members = $this->getRegionMembers($regionName);
    $members[strtolower($player->getName())] = $permission;
    return $this->storage->updateByID(strtolower($regionName), ['members' => $members]);
  }

  public function removeRegionMember(string $regionName, IPlayer $player): bool {
    if (!($this->regionExists($regionName)) || $this->hasRegionPermission($regionName, $player, self::PERMISSION_NOT_MEMBER)) return false;
    $members = $this->getRegionMembers($regionName);
    unset($members[strtolower($player->getName())]);
    return $this->storage->updateByID(strtolower($regionName), ['members' => $members]);
  }
}
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

namespace steinssquad\perms;


use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\IPlayer;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\PluginBase;
use steinssquad\perms\command\PrefixCommand;
use steinssquad\perms\command\SetGroupCommand;
use steinssquad\perms\model\Group;
use steinssquad\steinscore\Loader;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\steinscore\utils\Storage;
use steinssquad\steinscore\utils\Translator;


class SteinsPerms extends PluginBase implements Listener {

  /** @var SteinsPerms */
  public static $instance;

  //TODO: в донат кейсах выдавать, когда окупаешься и в /hack тоже.
  public static $profit = 0;


  /** @var Storage */
  private $storage;

  private $attachments = [];

  public function onLoad() {
    Translator::registerLanguages('permission', $this->getFile() . 'resources/languages/');
  }

  public function onEnable() {
    self::$instance = $this;
    $this->saveResource('config.yml', Loader::TEST_BUILD);
    $priority = 0;
    foreach ($this->getConfig()->get('groups') as $groupName => $groupData) {
      Group::registerGroup(
        $groupName,
        $groupData['priority'] ?? $priority,
        !isset($groupData['inheritance']) ? [] : (is_array($groupData['inheritance']) ? $groupData['inheritance'] : [$groupData['inheritance']]),
        $groupData['permissions'] ?? [],
        $groupData['prefix'] ?? null,
        $groupData['suffix'] ?? null,
        $groupData['chat'] ?? null,
        $groupData['nametag'] ?? null,
        $groupData['default-money'] ?? null,
        $groupData['region-count'] ?? null,
        $groupData['region-size'] ?? null
      );
      ++$priority;
    }
    Group::initAll();
    $this->getServer()->getCommandMap()->register('steinscore/permission', new SetGroupCommand());
    $this->getServer()->getCommandMap()->register('steinscore/permission', new PrefixCommand());
    $this->storage = new Storage($this->getDataFolder() . 'perms.db', ['group' => $this->getDefaultGroup()->getName(), 'permissions' => [], 'until' => null, 'prevGroup' => $this->getDefaultGroup()->getName(), 'prevUntil' => null]);
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }

  public function onDisable() {
    $this->storage->save();
  }

  public function getDefaultGroup(): Group {
    foreach ($this->getConfig()->get('groups') as $groupName => $data) {
      if (isset($data['isDefault']) and $data['isDefault']) return Group::getGroup($groupName);
    }
    throw new \RuntimeException();
  }

  public function groupExists(string $group): bool {
    return Group::getGroup($group) !== null;
  }

  public function getGroup(IPlayer $player): Group {
    if ($player instanceof SteinsPlayer) return $player->getGroup();
    $data = $this->storage->findByID(strtolower($player->getName()));
    if ($data !== null) return $this->groupExists($data['group']) ? Group::getGroup($data['group']) : $this->getDefaultGroup();
    return $this->getDefaultGroup();
  }

  public function getGroupUntil(IPlayer $player): ?int {
    $data = $this->storage->findByID(strtolower($player->getName()));
    return $data !== null ? $data['until'] : null;
  }

  public function getPrevGroup(IPlayer $player): ?Group {
    $data = $this->storage->findByID(strtolower($player->getName()));
    if ($data === null) return null;
    return $this->groupExists($data['prevGroup']) ? Group::getGroup($data['prevGroup']) : $this->getDefaultGroup();
  }

  public function getPrevUntil(IPlayer $player): ?int {
    $data = $this->storage->findByID(strtolower($player->getName()));
    return $data !== null ? $data['prevUntil'] : null;
  }

  public function setGroup(IPlayer $player, Group $group = null, int $timestamp = null) {
    $group = $group ?? $this->getDefaultGroup();
    $this->setGroupInternal($player, $group, $timestamp, $this->getGroup($player), $this->getGroupUntil($player));
    if ($player instanceof SteinsPlayer) {
      $player->changeGroup($group);
      $this->updatePermissions($player);
    }
  }

  private function setGroupInternal(IPlayer $player, Group $group, int $timestamp = null, Group $oldGroup = null, int $oldTimestamp = null) {
    if ((is_null($oldGroup) || $oldGroup === $this->getDefaultGroup()) && $group === $this->getDefaultGroup()) {
      if (count($this->storage->findByID(strtolower($player->getName()))['permissions']) === 0) {
        return $this->storage->deleteByID(strtolower($player->getName()));
      }
    }
    if ($this->storage->exists(strtolower($player->getName()))) {
      return $this->storage->updateByID(strtolower($player->getName()), [
        'group' => $group->getName(),
        'prevGroup' => ($oldGroup ?? $this->getDefaultGroup())->getName(),
        'until' => is_null($timestamp) ? null : time() + $timestamp,
        'prevUntil' => is_null($oldTimestamp) ? null : $oldTimestamp - time()
      ]);
    }
    return $this->storage->insert(strtolower($player->getName()), [
      'group' => $group->getName(),
      'prevGroup' => ($oldGroup ?? $this->getDefaultGroup())->getName(),
      'until' => is_null($timestamp) ? null : time() + $timestamp,
      'prevUntil' => is_null($oldTimestamp) ? null : $oldTimestamp - time()
    ]);
  }

  public function handlePlayerLoginEvent(PlayerLoginEvent $event) {
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();
    if (!isset($this->attachments[$player->getUniqueId()->toString()])) {
      $this->attachments[$player->getUniqueId()->toString()] = $player->addAttachment($this);
    }
    if ($this->getGroupUntil($player) !== null && time() >= $this->getGroupUntil($player)) {
      $this->setGroupInternal($player, $this->getPrevGroup($player), $this->getPrevUntil($player));
    }
    $data = $this->storage->findByID($player->getLowerCaseName());
    $player->changeGroup($data === null ? $this->getDefaultGroup() : Group::getGroup($data['group']));
    $this->updatePermissions($player);
  }

  public function getPlayerPermissions(IPlayer $player): array {
    $data = $this->storage->findByID(strtolower($player->getName()));
    if (is_array($data) && count($data['permissions']) > 0) {
      return $data['permissions'];
    }
    return [];
  }

  public function updatePermissions(SteinsPlayer $player) {
    $permissions = [];
    foreach (($group = $this->getGroup($player))->getPermissions() as $permission) {
      if ($permission === '*') {
        foreach (PermissionManager::getInstance()->getPermissions() as $tmp)
          $permissions[$tmp->getName()] = true;
      } else {
        $isNegative = substr($permission, 0, 1) === "-";
        $permissions[substr($permission, $isNegative ? 1 : 0)] = !$isNegative;
      }
    }
    foreach ($this->getPlayerPermissions($player) as $permission) {
      if ($permission === '*') {
        foreach (PermissionManager::getInstance()->getPermissions() as $tmp)
          $permissions[$tmp->getName()] = true;
      } else {
        $isNegative = substr($permission, 0, 1) === "-";
        $permissions[substr($permission, $isNegative ? 1 : 0)] = !$isNegative;
      }
    }
    if (isset($this->attachments[$player->getUniqueId()->toString()])) {
      $attachment = $this->attachments[$player->getUniqueId()->toString()];
      $attachment->clearPermissions();
      $attachment->setPermissions($permissions);
    }
  }
}
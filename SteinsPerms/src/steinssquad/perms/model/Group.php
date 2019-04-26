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

namespace steinssquad\perms\model;


use pocketmine\utils\TextFormat;


class Group {

  /** @var Group[] */
  private static $groups = [];
  public static function registerGroup(string $group, int $priority, array $inheritance, array $permissions, ?string $prefix, ?string $suffix, ?string $chat, ?string $nametag, ?int $defaultMoney, ?int $regionCount, ?int $regionSize): Group {
    return self::$groups[$group] = new Group($group, $priority, $inheritance, $permissions, $prefix, $suffix, $chat, $nametag, $defaultMoney, $regionCount, $regionSize);
  }

  public static function initAll() {
    foreach (self::$groups as $group) $group->init();
  }

  public static function getGroup(string $groupName): ?Group {
    return self::$groups[$groupName] ?? null;
  }

  public static function getGroups(): array {
    return self::$groups;
  }

  private $initialized = false;

  private $name = '';
  private $priority = 0;
  private $permissions = [];
  private $inheritance = [];
  private $prefix = '';
  private $suffix = '';
  private $chatFormat;
  private $nametagFormat;
  private $defaultMoney;
  private $regionCount;
  private $regionSize;

  public function __construct(string $name, int $priority, array $inheritance, array $permissions, ?string $prefix, ?string $suffix, ?string $chat, ?string $nametag, ?int $defaultMoney, ?int $regionCount, ?int $regionSize) {
    $this->name = $name;
    $this->priority = $priority;
    $this->inheritance = $inheritance;
    $this->permissions = $permissions;
    $this->prefix = $prefix;
    $this->suffix = $suffix;
    if ($chat !== null) $this->setChatFormat($chat);
    if ($nametag !== null) $this->setNametagFormat($nametag);
    $this->defaultMoney = $defaultMoney;
    $this->regionCount = $regionCount;
    $this->regionSize  = $regionSize;
  }

  /** Hack.  */
  public function init() {
    if (!$this->initialized) {
      $this->permissions = array_merge($this->permissions, ...array_map(function (string $inherit) {
        if (!self::$groups[$inherit]->initialized) {
          self::$groups[$inherit]->init();
        }
        return self::$groups[$inherit]->getPermissions();
      }, $this->inheritance));
      foreach ($this->inheritance as $inherit) {
        if ($this->chatFormat === null && self::$groups[$inherit]->chatFormat !== null) $this->chatFormat = self::$groups[$inherit]->chatFormat;
        if ($this->nametagFormat === null && self::$groups[$inherit]->nametagFormat !== null) $this->nametagFormat = self::$groups[$inherit]->nametagFormat;
        if ($this->defaultMoney === null) $this->defaultMoney = self::$groups[$inherit]->defaultMoney;
        if ($this->regionCount === null) $this->regionCount = self::$groups[$inherit]->regionCount;
        if ($this->regionSize === null) $this->regionSize = self::$groups[$inherit]->regionSize;
      }
      $this->initialized = true;
    }
  }

  public function getName(): string {
    return $this->name;
  }

  public function getPriority(): int {
    return $this->priority;
  }

  public function getPermissions(): array {
    return $this->permissions;
  }

  public function getPrefix(): string {
    return $this->prefix;
  }

  public function getSuffix(): string {
    return $this->suffix;
  }

  public function getChatFormat(array $placeholders = []): string {
    $ret = $this->chatFormat;
    foreach ($placeholders as $k => $v) $ret = str_replace("{{$k}}", $v, $ret);
    return TextFormat::colorize($ret);
  }

  public function setChatFormat(string $chat): void {
    $this->chatFormat = $chat;
  }

  public function getNametagFormat(array $placeholders = []): string {
    $ret = $this->nametagFormat;
    foreach ($placeholders as $k => $v) $ret = str_replace("{{$k}}", $v, $ret);
    return TextFormat::colorize($ret);
  }

  public function setNametagFormat(string $nametag): void {
    $this->nametagFormat = $nametag;
  }

  public function getDefaultMoney(): int {
    return $this->defaultMoney;
  }

  public function setDefaultMoney(int $defaultMoney): void {
    $this->defaultMoney = $defaultMoney;
  }

  public function getRegionCount(): int {
    return $this->regionCount;
  }

  public function setRegionCount(int $regionCount): void {
    $this->regionCount = $regionCount;
  }


  public function getRegionSize(): int {
    return $this->regionSize;
  }

  public function setRegionSize(int $regionSize): void {
    $this->regionSize = $regionSize;
  }
}
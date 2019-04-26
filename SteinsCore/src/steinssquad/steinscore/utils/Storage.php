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

namespace steinssquad\steinscore\utils;


class Storage {

  private $storageFile;
  private $structure = array();
  private $storage = array();

  public function __construct(string $storageFile, array $structure = array()) {
    if (!file_exists(dirname($storageFile))) {
      mkdir($storageFile, 0777, true);
    }
    $this->storageFile = $storageFile;
    $this->structure = $structure;
    if (file_exists($storageFile)) {
      $this->storage = json_decode(file_get_contents($this->storageFile), true);
    }
  }

  public function exists($uniqueID): bool {
    return isset($this->storage[$uniqueID]);
  }

  public function findOne(\Closure $closure = null): ?array {
    foreach ($this->storage as $uniqueID => $val) {
      if (is_null($closure) || $closure($val, $uniqueID)) {
        return $val;
      }
    }
    return null;
  }
  public function find(\Closure $closure = null, int $limit = -1, int $offset = 0): array {
    $ret = [];
    $i = 0;
    foreach ($this->storage as $uniqueID => $val) {
      if (is_null($closure) || $closure($val, $uniqueID)) {
        if (++$i < $offset) continue;
        $ret[$uniqueID] = $val;
        if (count($ret) === $limit && $limit > 0) break;
      }
    }
    return $ret;
  }
  public function findByID($uniqueID): ?array {
    if (isset($this->storage[$uniqueID])) {
      return $this->storage[$uniqueID];
    }
    return null;
  }
  public function findID(\Closure $closure) {
    foreach ($this->storage as $uniqueID => $val) {
      if ($closure($val, $uniqueID)) {
        return $uniqueID;
      }
    }
    return null;
  }

  public function findSorted(\Closure $closure, int $limit = -1, int $offset = 0): array {
    $arr = $this->storage;
    uasort($arr, $closure);
    $ret = [];
    $i = 0;
    foreach ($arr as $uniqueID => $val) {
      if (++$i < $offset) continue;
      $ret[$uniqueID] = $val;
      if (count($ret) === $limit && $limit > 0) break;
    }
    return $ret;
  }

  public function count(\Closure $closure = null, int $limit = -1, int $offset = 0): int {
    $ret = 0;
    $i = 0;
    foreach ($this->storage as $uniqueID => $val) {
      if (is_null($closure) || $closure($val, $uniqueID)) {
        if (++$i < $offset) continue;
        if (++$ret === $limit && $limit > 0) {
          break;
        }
      }
    }
    return $ret;
  }

  public function update(\Closure $closure = null): bool {
    $ret = false;
    foreach ($this->storage as $uniqueID => &$val) {
      if (is_null($closure) || $closure($val, $uniqueID)) $ret = $ret || true;
    }
    return $ret;
  }
  public function updateByID($uniqueID, array $keys): bool {
    if (isset($this->storage[$uniqueID])) {
      $this->storage[$uniqueID] = array_replace($this->storage[$uniqueID], $keys);
      return true;
    }
    return false;
  }

  public function delete(\Closure $closure = null): bool {
    $ret = false;
    foreach ($this->storage as $uniqueID => $val) {
      if (is_null($closure) || $closure($val, $uniqueID)) {
        unset($this->storage[$uniqueID]);
        if(!$ret) $ret = true;
      }
    }
    return $ret;
  }
  public function deleteByID($uniqueID): bool {
    if (isset($this->storage[$uniqueID])) {
      unset($this->storage[$uniqueID]);
      return true;
    }
    return false;
  }

  public function insert($uniqueID, array $data = []): bool {
    if (!isset($this->storage[$uniqueID])) {
      $this->storage[$uniqueID] = array_merge($this->structure, $data);
      return true;
    }
    return false;
  }

  public function save() {
    file_put_contents($this->storageFile, json_encode($this->storage, JSON_UNESCAPED_UNICODE));
  }

  public function getStorageFile(): string {
    return $this->storageFile;
  }
}
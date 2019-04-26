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


use pocketmine\level\Position;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Server;


class ParseUtils {

  public static function timestampFromString(string $time): int {
    preg_match_all("/[0-9]+(month|месяц|week|недел|day|дн|день|hour|час|minute|минут|second|секунд)|[0-9]+/", $time, $matches);
    $timestamp = 0;
    foreach ($matches[0] as $matchID => $match) {
      $unit = $matches[1][$matchID];
      if ($unit === '' || $unit === 'second' || $unit === 'секунд') {
        $timestamp += intval(str_replace($unit, "", $matches[0][$matchID]));
      } else if ($unit === 'minute' || $unit === 'минут') {
        $timestamp += intval(str_replace($unit, "", $matches[0][$matchID])) * 60;
      } else if ($unit === 'hour' || $unit === 'час') {
        $timestamp += intval(str_replace($unit, "", $matches[0][$matchID])) * 3600;
      } else if ($unit === 'day' || $unit === 'дн' || $unit === 'день') {
        $timestamp += intval(str_replace($unit, "", $matches[0][$matchID])) * 86400;
      } else if ($unit === 'week' || $unit === 'недел') {
        $timestamp += intval(str_replace($unit, "", $matches[0][$matchID])) * 604800;
      } else if ($unit === 'month' || $unit === 'месяц') {
        $timestamp += intval(str_replace($unit, "", $matches[0][$matchID])) * 2592000;
      }
    }

    return $timestamp;
  }

  public static function placeholderFromTimestamp(int $timestamp, $language) {
    $placeholders = [];
    if (($months = intdiv($timestamp, 2592000)) > 0) {
      $timestamp -= $months * 2592000;
      $placeholders['generic.time-months'] = $months;
    }
    if (($weeks = intdiv($timestamp, 604800)) > 0) {
      $timestamp -= $weeks * 604800;
      $placeholders['generic.time-weeks'] = $weeks;
    }
    if (($days = intdiv($timestamp, 86400)) > 0) {
      $timestamp -= $days * 86400;
      $placeholders['generic.time-days'] = $days;
    }
    if (($hours = intdiv($timestamp, 3600)) > 0) {
      $timestamp -= $hours * 3600;
      $placeholders['generic.time-hours'] = $hours;
    }
    if (($minutes = intdiv($timestamp, 60)) > 0) {
      $timestamp -= $minutes * 60;
      $placeholders['generic.time-minutes'] = $minutes;
    }
    if ($timestamp > 0) $placeholders['generic.time-seconds'] = $timestamp;
    $ret = [];
    if (count($placeholders) > 0) {
      foreach ($placeholders as $placeholder => $amount) {
        $ret[] = Translator::translate($language, $placeholder, ['amount' => $amount]);
      }
    }
    return implode(', ', $ret);
  }

  public static function homesFromCompound(CompoundTag $tag = null): array {
    $ret = [];
    if ($tag !== null) {
      /** @var CompoundTag $value */
      foreach ($tag as $name => $value) {
        $position = $value->getListTag('position');
        $level    = $value->getString('level', Server::getInstance()->getDefaultLevel()->getFolderName());
        $ret[$name] = new Position($position[0], $position[1], $position[2], Server::getInstance()->getLevelByName($level));
      }
    }
    return $ret;
  }

  public static function compoundFromHomes(array $homes = []): CompoundTag {
    $tag = new CompoundTag('homes');
    /** @var Position $position */
    foreach ($homes as $home => $position) {
      $tag->setTag(new CompoundTag($home, [
        "position" => new ListTag("position", [
          new DoubleTag("", $position->getX()),
          new DoubleTag("", $position->getY()),
          new DoubleTag("", $position->getZ())
        ]),
        "level" => new StringTag("level", $position->getLevel()->getFolderName())
      ]));
    }
    return $tag;
  }

  public static function backPositionFromCompound(CompoundTag $tag = null): Position {
    $position = new Position();
    if ($tag) {
      $pos = $tag->getListTag('position');
      $position->setComponents($pos[0], $pos[1], $pos[2]);
      $position->setLevel(Server::getInstance()->getLevelByName($tag->getString('level', Server::getInstance()->getDefaultLevel()->getFolderName())));
    }
    return $position;
  }

  public static function compoundFromBackPosition(Position $position): CompoundTag {
    return new CompoundTag('backPosition', [
      'position' => new ListTag('position', [
        new DoubleTag("", $position->getX()),
        new DoubleTag("", $position->getY()),
        new DoubleTag("", $position->getZ())
      ]),
      'level' => new StringTag('level', $position->getLevel() === null ? Server::getInstance()->getDefaultLevel()->getFolderName() : $position->getLevel()->getFolderName())
    ]);
  }
}
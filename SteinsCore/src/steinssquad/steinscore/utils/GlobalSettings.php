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


use pocketmine\block\BlockIds;
use pocketmine\level\Position;
use pocketmine\Server;


class GlobalSettings {

  private static $settings = [];

  public static function init() {
    self::$settings = [
      'warps' => [
        'mine' => new Position(292, 65, 23, Server::getInstance()->getDefaultLevel()),
      ],
      'mine' => [
        'min' => [283, 62, 233],
        'max' => [292, 64, 242],
      ],
      'holograms' => [
        [
          'title' => '%feature.hologram-mine',
          'position' => new Position(292, 67, 238, Server::getInstance()->getDefaultLevel()),
        ], [
          'title' => '%feature.hologram-commands',
          'texts' => [
            '%feature.hologram-commands-1',
            '%feature.hologram-commands-2',
            '%feature.hologram-commands-3',
            '%feature.hologram-commands-4',
            '%feature.hologram-commands-5'
          ],
          'position' => new Position(305, 70, 276, Server::getInstance()->getDefaultLevel())
        ]
      ],
      'refs' => [
        'saloeater' => 'setgroup {username} vip 3 days'
      ],
      'jobs' => [
        'miner' => ['place' => [
          BlockIds::STONE => 10,
          BlockIds::GOLD_ORE => 10,
          BlockIds::IRON_ORE => 10,
          BlockIds::COAL_ORE => 5,
          BlockIds::LAPIS_ORE => 30,
          BlockIds::DIAMOND_ORE => 50,
          BlockIds::REDSTONE_ORE => 25,
          BlockIds::EMERALD_ORE => 1000
        ]],
        'woodcutter' => ['break' => [
          BlockIds::PLANKS => 10,
          BlockIds::WOOD => 10,
          BlockIds::LEAVES => 10,
          BlockIds::WOOD2 => 20,
          BlockIds::LEAVES2 => 20
        ]]
      ],
      'jail' => new Position(266, 65, 303,  Server::getInstance()->getDefaultLevel())
    ];
  }

  public static function get(string $node) {
    return self::$settings[$node] ?? null;
  }
}
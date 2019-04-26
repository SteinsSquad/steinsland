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


class AchievementList {

  public const MAP = [
    //builder (сломанные блоки отправляются сразу в инвентарь)
    'buildWorkBench' => [],
    'buildPickaxe' => ['buildWorkBench'],
    'buildFurnace' => ['buildPickaxe'],
    'buildBetterPickaxe' => ['buildPickaxe'],
    'diamond' => ['buildBetterPickaxe'],

    //farmer (убирает потребность в еде)
    'buildHoe' => ['buildWorkBench'],
    'makeBread' => ['buildHoe'],
    'bakeCake' => ['makeBread'],

    //hunter (1 доп сердечко)
    "buildSword" => ["buildWorkBench"],
    "firstKill" => ["buildSword"],
    "streak" => ["firstKill"],
  ];
}
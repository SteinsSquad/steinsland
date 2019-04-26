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

namespace steinssquad\feature\task;


use pocketmine\scheduler\Task;
use steinssquad\feature\SteinsFeature;
use steinssquad\steinscore\player\SteinsPlayer;


class QuizTask extends Task {

  public function onRun(int $currentTick) {
    $action = mt_rand(0, 2);

    $first = $action === 2 ? mt_rand(-10, 10) : mt_rand(-100, 100);
    $second = $action === 2 ? mt_rand(-10, 10) : mt_rand(-100, 100);

    SteinsPlayer::broadcast('feature.quiz-new', ['first' => $first, 'second' => $second, 'action' => $action === 0 ? '+' : ($action === 1 ? '-' : '*')]);
    SteinsFeature::$quizResult = $action === 0 ? ($first + $second) : ($action === 1 ? $first - $second : $first * $second);
  }
}
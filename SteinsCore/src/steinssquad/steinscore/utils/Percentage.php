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


class Percentage {

  private $values;
  private $sum;

  public function __construct(array $values) {
    $this->values = $values;
    foreach ($this->values as $value) {
      $this->sum += floor($value['chance']);
    }
  }

  public function nextRandom() {
    shuffle($this->values);
    $current = mt_rand(1, $this->sum);
    foreach ($this->values as $value) {
      $current -= $value['chance'];
      if ($current <= 0) break;
    }

    return $value['item'] ?? null;
  }
}
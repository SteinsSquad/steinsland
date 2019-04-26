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

namespace steinssquad\feature\command;


use pocketmine\command\CommandSender;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class SizeCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('size', 'feature', 'steinscore.feature.size');

    $this->registerOverload(['name' => 'size', 'type' => 'rawtext', 'enum' => [
      'name' => 'sizes',
      'values' => ['dwarf', 'normal', 'giant'],
    ], 'optional' => true]);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    $size = strtolower(array_shift($args) ?? 'normal');
    if ($size !== 'dwarf' && $size !== 'normal' && $size !== 'giant') return self::RESULT_USAGE;
    $sizeList = ['dwarf' => 0.8, 'normal' => 1.0, 'giant' => 1.2];
    if ($sender->getScale() === $sizeList[$size]) {
      $sender->sendLocalizedMessage('feature.size-failed');
      return self::RESULT_SUCCESS;
    }
    $sender->setScale($sizeList[$size]);
    $sender->sendLocalizedMessage("feature.size-success-$size");
    return self::RESULT_SUCCESS;
  }
}
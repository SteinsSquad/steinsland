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

namespace steinssquad\feature\command\helpful;


use pocketmine\command\CommandSender;
use pocketmine\entity\Attribute;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class SpeedCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('speed', 'feature', 'steinscore.feature.speed');
    $this->registerOverload(['name' => '1-10', 'type' => 'int']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    $speed = array_shift($args) ?? 1;
    if (!is_numeric($speed) || $speed < 1 || $speed > 10) return self::RESULT_USAGE;
    $sender->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->setValue($speed / 10, false, true);
    $sender->sendLocalizedMessage('feature.speed-success', ['speed' => $speed]);
    return self::RESULT_SUCCESS;
  }
}
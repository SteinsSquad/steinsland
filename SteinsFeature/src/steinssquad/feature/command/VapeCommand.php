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
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\level\particle\SmokeParticle;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\steinscore\utils\ParseUtils;


class VapeCommand extends CustomCommand {

  private $uses = [];

  public function __construct() {
    parent::__construct('vape', 'feature', 'steinscore.feature.vape');
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (!$sender->hasMoney(1000)) return self::RESULT_NOT_ENOUGH_MONEY;
    if (isset($this->uses[$sender->getLowerCaseName()]) && ($time = $this->uses[$sender->getLowerCaseName()]) > time()) {
      $sender->sendLocalizedMessage('generic.cooldown', ['time' => ParseUtils::placeholderFromTimestamp($time - time(), $sender)]);
      return self::RESULT_SUCCESS;
    }
    $sender->sendLocalizedMessage('feature.vape-success', ['machine' => ['Voopoo Drag 157W', 'Eleaf iJust S Kit', 'Eleaf iJust 3 Kit', 'Eleaf iKuu i200'][$rand = mt_rand(0, 3)]]);
    $sender->addEffect(new EffectInstance(Effect::getEffect(Effect::SPEED), 20 * 60 * ($rand + 1), ceil((1 + $rand) / 2), false));
    $sender->addEffect(new EffectInstance(Effect::getEffect(Effect::FATIGUE), 20 * 60 * ($rand + 1), ceil((1 + $rand) / 2), false));
    $particle = new SmokeParticle($sender);
    for ($i = 0; $i <= 10; $i++) {
      $particle->y += $i / 50;
      $particle->x += ($i % 2 === 0 ? 1 : -1) * $i / 50;
      $particle->z += ($i % 2 === 0 ? -1 : 1) * $i / 50;
      $sender->getLevel()->addParticle($particle);
    }
    $sender->reduceMoney(1000);
    $this->uses[$sender->getLowerCaseName()] = time() + 1800;
    return self::RESULT_SUCCESS;
  }
}
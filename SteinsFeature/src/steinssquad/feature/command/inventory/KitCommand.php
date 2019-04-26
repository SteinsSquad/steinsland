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

namespace steinssquad\feature\command\inventory;


use pocketmine\command\CommandSender;
use steinssquad\feature\SteinsFeature;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\steinscore\utils\ParseUtils;


class KitCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('kit', 'feature', 'steinscore.feature.kit.use');

    $this->registerOverload(
      [
        'name' => 'kitName',
        'type' => 'string',
        'enum' => ['values' => SteinsFeature::$instance->getKits(), 'name' => 'kits'],
        'optional' => true
      ]);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if ($sender->isCreative()) return self::RESULT_NOT_FOR_CREATIVE;
    if (count($args) === 0) {
      $sender->sendLocalizedMessage('feature.kit-header', ['count' => count($kits = SteinsFeature::$instance->getKits())]);
      foreach ($kits as $kit) {
        if ($sender->hasPermission("steinscore.feature.kit-$kit")) {
          if (SteinsFeature::$instance->isInCooldown($sender, $kit)) $sender->sendLocalizedMessage('feature.kit-line-cooldown', ['kit' => $kit, 'time' => ParseUtils::placeholderFromTimestamp(SteinsFeature::$instance->getCooldown($sender, $kit), $sender)]);
          else $sender->sendLocalizedMessage('feature.kit-line-available', ['kit' => $kit]);
        }
      }
      return self::RESULT_SUCCESS;
    }
    if (!(SteinsFeature::$instance->kitExists($kit = array_shift($args)))) return self::RESULT_USAGE;
    if (!($sender->hasPermission("steinscore.feature.kit-$kit"))) return self::RESULT_NO_RIGHTS;
    if (!($sender->hasPermission('steinscore.feature.kit')) && SteinsFeature::$instance->isInCooldown($sender, $kit)) {
      $sender->sendLocalizedMessage('generic.cooldown', ['time' => ParseUtils::placeholderFromTimestamp(SteinsFeature::$instance->getCooldown($sender, $kit), $sender)]);
      return self::RESULT_SUCCESS;
    }
    $sender->sendLocalizedMessage(
      SteinsFeature::$instance->giveKit($sender, $kit, $sender->hasPermission('steinscore.feature.kit')) ?
        'feature.kit-success' : 'feature.kit-success-drop', ['kit' => $kit]);
    return self::RESULT_SUCCESS;
  }
}
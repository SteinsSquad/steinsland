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
use steinssquad\feature\SteinsFeature;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class RefCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('ref', 'feature','steinscore.feature.ref');
    $this->registerOverload(['name' => 'ref', 'type' => 'string']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (count($args) === 0) return self::RESULT_USAGE;

    if (SteinsFeature::$instance->hasActivatedRef($sender)) {
      $sender->sendLocalizedMessage('feature.ref-failed');
      return self::RESULT_SUCCESS;
    }

    $ref = strtolower(array_shift($args));
    if (SteinsFeature::$instance->getConfig()->getNested("refs.$ref") === null) {
      $sender->sendLocalizedMessage('feature.ref-not-found');
      return self::RESULT_SUCCESS;
    }
    SteinsFeature::$instance->setActivatedRef($sender, $ref);
    $sender->sendLocalizedMessage('feature.ref-success', ['ref' => $ref]);
    return self::RESULT_SUCCESS;
  }
}
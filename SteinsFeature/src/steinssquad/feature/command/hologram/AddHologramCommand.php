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

namespace steinssquad\feature\command\hologram;


use pocketmine\command\CommandSender;
use steinssquad\feature\SteinsFeature;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class AddHologramCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('addholo', 'feature', 'steinscore.feature.holograms.use');
    $this->registerOverload(['name' => 'text', 'type' => 'message']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (count($args) === 0) return self::RESULT_USAGE;
    $possibleHolograms = 1;
    if ($sender->hasPermission('steinscore.feature.holograms')) {
      $possibleHolograms = 10;
    } else if ($sender->hasPermission('steinscore.feature.holograms.5')) {
      $possibleHolograms = 5;
    } else if ($sender->hasPermission('steinscore.feature.holograms.3')) {
      $possibleHolograms = 3;
    }
    if (count(SteinsFeature::$instance->getHolograms($sender) ?? []) > $possibleHolograms) {
      $sender->sendLocalizedMessage('feature.addholo-failed');
      return self::RESULT_SUCCESS;
    }
    $text = str_replace('\n', "\n", implode(" ", $args));
    if (isset($text{0}) && $text{0} === '%' && !($sender->hasPermission('steinscore.feature'))) $text = substr($text, 1);

    SteinsFeature::$instance->addHologram($sender, $text);
    $sender->sendLocalizedMessage('feature.addholo-success');
    return self::RESULT_SUCCESS;
  }
}
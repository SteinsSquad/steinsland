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


class HologramsCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('holos', 'feature', 'steinscore.feature.holograms.use');
  }

  public function onCommand(CommandSender $sender, array $args) {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_USAGE;
    if (is_null($holos = SteinsFeature::$instance->getHolograms($sender)) || count($holos) === 0) {
      $sender->sendLocalizedMessage('feature.holos-failed');
      return self::RESULT_SUCCESS;
    }
    $sender->sendLocalizedMessage('feature.holos-success', ['pos' => implode("&f, &a", array_keys($holos))]);
    return self::RESULT_SUCCESS;
  }
}
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

namespace steinssquad\economy\command;


use pocketmine\command\CommandSender;
use steinssquad\economy\SteinsEconomy;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\steinscore\utils\GlobalSettings;


class JobCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('job', 'economy', 'steinscore.economy.job');
    $this->registerOverload(['name' => 'job', 'type' => 'rawtext', 'enum' => ['name' => 'jobs', 'values' => array_keys(GlobalSettings::get('jobs'))]]);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (count($args) === 0) return self::RESULT_USAGE;
    if (!(isset(SteinsEconomy::$instance->getConfig()->get('jobs')[$job = strtolower(array_shift($args))]))) return self::RESULT_USAGE;
    if ($sender->job !== $job) {
      $sender->job = $job;
      $sender->sendLocalizedMessage('economy.job-success', ['job' => $job]);
    } else $sender->sendLocalizedMessage('economy.job-failed');
    return self::RESULT_SUCCESS;
  }
}
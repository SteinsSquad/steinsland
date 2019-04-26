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

namespace steinssquad\admin\command\jail;


use pocketmine\command\CommandSender;
use steinssquad\admin\SteinsAdmin;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class JailbreakCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('jailbreak', 'admin', 'steinscore.admin.jailbreak', ['jb']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (SteinsAdmin::$instance->isJailed($sender)) {
      $success = mt_rand(0, 9999) === 0;
      if ($success) SteinsAdmin::$instance->unjailPlayer($sender);
      $sender->sendLocalizedMessage($success ? 'admin.jailbreak-success' : 'admin.jailbreak-failed');
    } else $sender->sendLocalizedMessage('admin.jailbreak-error');
    return self::RESULT_SUCCESS;
  }
}
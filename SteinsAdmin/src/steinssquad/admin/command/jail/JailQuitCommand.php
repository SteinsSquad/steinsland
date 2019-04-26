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
use steinssquad\steinscore\utils\ParseUtils;


class JailQuitCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('jailquit', 'admin','steinscore.admin.jailquit', ['jq']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (SteinsAdmin::$instance->isJailed($sender) && SteinsAdmin::$instance->getJailTime($sender) > time()) {
      $sender->sendLocalizedMessage('generic.cooldown', ['time' => ParseUtils::placeholderFromTimestamp(
        SteinsAdmin::$instance->getJailTime($sender) - time(), $sender
      )]);
      return self::RESULT_SUCCESS;
    }
    if (SteinsAdmin::$instance->unjailPlayer($sender)) $sender->sendLocalizedMessage('admin.jailquit-success');
    else $sender->sendLocalizedMessage('admin.jailquit-failed');

    return self::RESULT_SUCCESS;
  }
}
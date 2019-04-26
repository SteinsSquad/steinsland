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

namespace steinssquad\auth\command;


use pocketmine\command\CommandSender;
use steinssquad\auth\form\LoginForm;
use steinssquad\auth\SteinsAuth;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class LoginCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('login', 'auth', 'steinscore.auth.login');
    $this->registerOverload(['name' => 'password', 'type' => 'string', 'optional' => true]);
  }

  public function onCommand(CommandSender $sender, array $args) {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if (SteinsAuth::$instance->isPlayerAuthenticated($sender)) {
      $sender->sendLocalizedMessage('auth.already-logged-in');
      return self::RESULT_SUCCESS;
    }
    if (count($args) === 0) {
      $sender->sendForm(new LoginForm($sender));
      return self::RESULT_SUCCESS;
    }
    SteinsAuth::$instance->authenticatePlayer($sender, $args[0]);
    return self::RESULT_SUCCESS;
  }

}
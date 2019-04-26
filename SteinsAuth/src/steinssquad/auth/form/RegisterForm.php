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

namespace steinssquad\auth\form;


use pocketmine\form\Form;
use pocketmine\Player;
use steinssquad\auth\SteinsAuth;
use steinssquad\steinscore\player\SteinsPlayer;


class RegisterForm implements Form {

  private $player;

  public function __construct(SteinsPlayer $player) {
    $this->player = $player;
  }

  public function handleResponse(Player $player, $data): void {
    /** @var SteinsPlayer $player */
    if (empty($data[1]) || $data[1] !== $data[2]) {
      $player->sendLocalizedMessage('auth.register-failed');
      return;
    }
    SteinsAuth::$instance->registerPlayer($player, $data[1]);
  }

  public function jsonSerialize() {
    return [
      'type' => 'custom_form',
      'title' => $this->player->localize('auth.register-form-title', ['player' => $this->player->getCurrentName()]),
      'content' => [
        ['type' => 'label', 'text' => $this->player->localize('auth.register-form-introduction', ['player' => $this->player->getCurrentName()])],
        ['type' => 'input', 'text' => $this->player->localize('auth.register-form-password')],
        ['type' => 'input', 'text' => $this->player->localize('auth.register-form-confirm')]
      ]
    ];
  }
}
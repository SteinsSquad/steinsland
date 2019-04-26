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


class ChangePasswordForm implements Form {

  private $player;

  public function __construct(SteinsPlayer $player) {
    $this->player = $player;
  }

  public function handleResponse(Player $player, $data): void {
    /** @var SteinsPlayer $player */
    if (empty($data[0]) || $data[0] !== $data[1]) {
      $player->sendLocalizedMessage('auth.changepass-failed');
      return;
    }
    SteinsAuth::$instance->changePlayerPassword($player, $data[0]);
  }


  public function jsonSerialize() {
    return [
      'type' => 'custom_form',
      'title' => $this->player->localize('auth.changepass-form-title', ['player' => $this->player->getCurrentName()]),
      'content' => [
        ['type' => 'input', 'text' => $this->player->localize('auth.changepass-form-password'), 'placeholder' => '********'],
        ['type' => 'input', 'text' => $this->player->localize('auth.changepass-form-confirm'), 'placeholder' => '********']
      ]
    ];
  }
}
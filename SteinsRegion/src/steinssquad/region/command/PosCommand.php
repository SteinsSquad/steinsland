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

namespace steinssquad\region\command;


use pocketmine\command\CommandSender;
use steinssquad\region\SteinsRegion;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class PosCommand extends CustomCommand {

  private $num;

  public function __construct(int $num) {
    parent::__construct("pos$num", 'region', 'steinscore.region.claim');
    $this->num = $num;
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    SteinsRegion::$instance->setSelection($sender, $this->num - 1, $sender->asPosition());
    $sender->sendLocalizedMessage("region.pos-success-$this->num");
    return self::RESULT_SUCCESS;
  }

}
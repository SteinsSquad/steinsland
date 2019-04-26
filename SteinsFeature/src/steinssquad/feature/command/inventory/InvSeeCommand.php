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

namespace steinssquad\feature\command\inventory;


use pocketmine\block\Block;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\inventory\VirtualInventory;
use steinssquad\steinscore\player\SteinsPlayer;


class InvSeeCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('invsee', 'feature', 'steinscore.feature.invsee');
  }

  public function onCommand(CommandSender $sender, array $args): bool {
    if (!($sender instanceof SteinsPlayer)) {
      $sender->sendMessage($this->translate('generic.in-game'));
      return true;
    }
    //TODO
    $sender->addWindow($sender->getInventory());
    return true;
  }
}
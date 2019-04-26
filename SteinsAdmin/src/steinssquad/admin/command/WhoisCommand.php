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

namespace steinssquad\admin\command;


use pocketmine\command\CommandSender;
use pocketmine\Server;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class WhoisCommand extends CustomCommand {

  public function onCommand(CommandSender $sender, array $args): bool {
    if (count($args) < 0) return false;
    if (!Server::getInstance()->hasOfflinePlayerData($playerName = array_shift($args))) {
      $sender->sendMessage($this->translate('generic.player-not-found'));
      return true;
    }
    $player = Server::getInstance()->getOfflinePlayer($playerName);

    $sender->sendMessage($this->translate('admin.whois-header', ['player' => $player->getName()]));
    $sender->sendMessage($this->translate('admin.whois-online', ['online' => $player instanceof SteinsPlayer ? $player->getAddress() . ':' . $player->getPort() : '-']));
    if ($player instanceof SteinsPlayer) $sender->sendMessage($this->translate('admin.whois-ping', ['ping' => $player->getPing()]));
    $sender->sendMessage($this->translate('admin.whois-donate', ['donate' => 'да']));


    return true;
  }
}
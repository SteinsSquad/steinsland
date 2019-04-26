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

namespace steinssquad\admin\command\ban;


use pocketmine\command\CommandSender;
use pocketmine\Server;
use steinssquad\admin\SteinsAdmin;
use steinssquad\perms\SteinsPerms;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\steinscore\utils\ParseUtils;


class PardonCommand extends CustomCommand {

  private $pardonCooldown = [];


  public function __construct() {
    parent::__construct('pardon', 'admin','steinscore.admin.ban.use', ['unban']);
    $this->registerOverload(['name' => 'player', 'type' => 'string']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) === 0) return self::RESULT_USAGE;
    //if (!Server::getInstance()->hasOfflinePlayerData($player = array_shift($args))) return self::RESULT_PLAYER_NOT_FOUND;
    $player = Server::getInstance()->getOfflinePlayer(array_shift($args));
    if (!(SteinsAdmin::$instance->isBanned($player))) {
      $sender->sendMessage($this->translate('admin.pardon-failed'));
      return self::RESULT_SUCCESS;
    }
    if (!($sender->hasPermission('steinscore.admin.ban')) && strcasecmp(SteinsAdmin::$instance->getBanIssuer($player), $sender->getName()) === 0) {
      if (isset($this->pardonCooldown[strtolower($sender->getName())]) && $this->pardonCooldown[strtolower($sender->getName())] > time()) {
        $sender->sendMessage($this->translate('generic.cooldown',  ['time' => ParseUtils::placeholderFromTimestamp(
          $this->pardonCooldown[strtolower($sender->getName())] - time(), $sender
        )]));
        return self::RESULT_SUCCESS;
      }
      $this->pardonCooldown[strtolower($sender->getName())] = time() + 900;
    }
    if (
      SteinsAdmin::$instance->getBanIssuer($player) !== null &&
      $sender instanceof SteinsPlayer &&
      strcasecmp(SteinsAdmin::$instance->getBanIssuer($player), $sender->getName()) === 0
    ) {
      $issuer = Server::getInstance()->getOfflinePlayer(SteinsAdmin::$instance->getBanIssuer($player));
      if ($issuer !== null) {
        if ($sender->getGroup()->getPriority() < SteinsPerms::$instance->getGroup($issuer)->getPriority()) return self::RESULT_PLAYER_HAS_IMMUNITY;
      }
    }
    SteinsAdmin::$instance->pardonPlayer($player);
    $this->broadcast('admin.pardon-success', ['player' => $sender instanceof SteinsPlayer? $sender->getCurrentName(): $sender->getName(), 'target' => $player->getName()]);
    return self::RESULT_SUCCESS;
  }
}
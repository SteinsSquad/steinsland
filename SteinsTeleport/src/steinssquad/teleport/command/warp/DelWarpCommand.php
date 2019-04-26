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

namespace steinssquad\teleport\command\warp;


use pocketmine\command\CommandSender;
use steinssquad\perms\SteinsPerms;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\teleport\SteinsTeleport;


class DelWarpCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('delwarp', 'teleport', 'steinscore.teleport.setwarp.use');
    $this->registerOverload(['name' => 'warp', 'type' => 'string']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) === 0) return self::RESULT_USAGE;
    if (!(SteinsTeleport::$instance->warpExists($warp = array_shift($args)))) {
      $sender->sendMessage($this->translate('teleport.warp-failed'));
      return self::RESULT_SUCCESS;
    }
    if ($sender instanceof SteinsPlayer && !($sender->hasPermission('steinscore.teleport.setwarp'))) {
      $owner = SteinsTeleport::$instance->getWarp($warp)['owner'];
      if ($owner === null || SteinsPerms::$instance->getGroup($owner)->getPriority() > $sender->getGroup()->getPriority())
        return self::RESULT_NO_RIGHTS;
    }
    SteinsTeleport::$instance->deleteWarp($warp);
    $sender->sendMessage($this->translate('teleport.delwarp-success', ['warp' => $warp]));
    return self::RESULT_SUCCESS;
  }

}
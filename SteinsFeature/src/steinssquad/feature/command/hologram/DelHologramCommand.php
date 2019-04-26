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

namespace steinssquad\feature\command\hologram;


use pocketmine\command\CommandSender;
use pocketmine\IPlayer;
use pocketmine\level\Position;
use steinssquad\feature\SteinsFeature;
use steinssquad\perms\SteinsPerms;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\entity\Hologram;
use steinssquad\steinscore\player\SteinsPlayer;


class DelHologramCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('delholo', 'feature', 'steinscore.feature.holograms.use');
    $this->registerOverload(['name' => 'position', 'type' => 'position']);
  }

  public function onCommand(CommandSender $sender, array $args) {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    $holo = null;
    if (count($args) < 3) {
      $holo = Hologram::getHologram($sender);
    } else {
      $x = $this->getRelativeDouble($sender->x, array_shift($args));
      $y = $this->getRelativeDouble($sender->y, array_shift($args), 0, 256);
      $z = $this->getRelativeDouble($sender->z, array_shift($args));
      $holo = Hologram::getHologram(new Position($x, $y, $z, $sender->level));
    }
    if ($holo === null || $holo->getCreator() === null) {
      $sender->sendLocalizedMessage('feature.delholo-failed');
      return self::RESULT_SUCCESS;
    }
    $player = SteinsPlayer::getOfflinePlayerExact($holo->getCreator());
    if ($player instanceof IPlayer && SteinsPerms::$instance->getGroup($player)->getPriority() > $sender->getGroup()->getPriority()) return self::RESULT_NO_RIGHTS;

    SteinsFeature::$instance->removeHologram($holo);
    $sender->sendLocalizedMessage('feature.delholo-success');
    return self::RESULT_SUCCESS;
  }
}
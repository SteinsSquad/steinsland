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

namespace steinssquad\feature\command\troll;


use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class ShockCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('shock', 'feature', 'steinscore.feature.shock', ['thor', 'strike']);
    $this->registerOverload(['name' => 'player', 'type' => 'player']);
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) === 0) return self::RESULT_USAGE;
    $player = SteinsPlayer::getPlayerByName(array_shift($args));
    if (!($player instanceof SteinsPlayer)) return self::RESULT_PLAYER_NOT_FOUND;
    if ($sender instanceof SteinsPlayer && $player->getGroup()->getPriority() > $sender->getGroup()->getPriority()) return self::RESULT_PLAYER_HAS_IMMUNITY;
    $pk = new AddEntityPacket();
    $pk->entityRuntimeId = $pk->entityUniqueId = Entity::$entityCount++;
    $pk->type = EntityIds::LIGHTNING_BOLT;
    $pk->position = $player->asVector3();
    $player->getLevel()->addChunkPacket($player->getFloorX() >> 4, $player->getFloorZ() >> 4, $pk);
    $player->attack(new EntityDamageEvent($player, EntityDamageEvent::CAUSE_MAGIC, 3));
    $sender->sendMessage($this->translate('feature.shock-success', ['player' => $player->getCurrentName()]));
    return self::RESULT_SUCCESS;
  }
}
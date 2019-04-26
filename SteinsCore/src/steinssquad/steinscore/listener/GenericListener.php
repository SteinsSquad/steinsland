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

namespace steinssquad\steinscore\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\Server;
use steinssquad\admin\SteinsAdmin;
use steinssquad\steinscore\player\SteinsPlayer;

class GenericListener implements Listener {

  private $commandCooldown = [];

  public function handlePlayerCreationEvent(PlayerCreationEvent $event) {
    $event->setPlayerClass(SteinsPlayer::class);
  }

  /**
   * @priority LOWEST
   * @param PlayerChatEvent $event
   * @return bool
   */
  public function handlePlayerChatEvent(PlayerChatEvent $event): bool {
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();
    $player->chatPreprocess($event);
    return true;
  }

  public function handlePlayerCommandPreprocessEvent(PlayerCommandPreprocessEvent $event) {
    $message = $event->getMessage();
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();
    if (preg_match("#^\/([\/a-z0-9_-а-яё@\.,:;'\"]+)[ ]?(.*)$#i", $message, $matches) > 0) {
      if (Server::getInstance()->getCommandMap()->getCommand($command = strtolower($matches[1])) === null) {
        $player->sendLocalizedMessage('generic.command-not-found', ['command' => $command]);
        $event->setCancelled(true);
        return true;
      }
      if (SteinsAdmin::$instance->isJailed($player) && $command !== 'jailbreak' && $command !== 'jailquit' && $command !== 'jb' && $command !== 'jq') {
        $player->sendLocalizedMessage('admin.jail-use-error');
        $event->setCancelled(true);
        return true;
      }
      if (!$player->hasPermission('steinscore.feature.cmd-spam')) {
        if (isset($this->commandCooldown[$player->getLowerCaseName()]) && $this->commandCooldown[$player->getLowerCaseName()] > time()) {
          $player->sendLocalizedMessage('generic.spam-command-detected', ['seconds' => $this->commandCooldown[$player->getLowerCaseName()] - time()]);
          $event->setCancelled();
          return true;
        }
        $seconds = 3;
        if ($player->hasPermission('steinscore.feature.cmd-spam.1')) {
          $seconds = 1;
        } else if ($player->hasPermission('steinscore.feature.cmd-spam.2')) {
          $seconds = 2;
        }
        $this->commandCooldown[$player->getLowerCaseName()] = time() + $seconds;
      }
    }
    return true;
  }
}
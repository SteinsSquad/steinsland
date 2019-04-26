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
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class ReportCommand extends CustomCommand {

  private $tickets = [];

  public function __construct() {
    parent::__construct('report', 'admin', 'steinscore.admin.report.use', ['ticket']);
    $this->registerOverload(['name' => 'report', 'type' => 'message']);
    $this->registerPermissibleOverload(
      [
        'steinscore.admin',
        'steinscore.admin.report'
      ], [
        'name' => 'sub', 'enum' => ['name' => 'cmd', 'values' => ['list', 'reply']]
      ], [
        'name' => 'player', 'type' => 'player', 'optional' => true
      ], [
        'name' => 'reply', 'type' => 'message', 'optional' => true
      ]
    );
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (count($args) === 0) return self::RESULT_SUCCESS;
    if ($sender->hasPermission('steinscore.admin.report') || !($sender instanceof SteinsPlayer)) {
      $action = array_shift($args);
      if ($action === 'list') {
        $sender->sendMessage($this->translate('admin.report-list-header', ['count' => count($this->tickets)]));
        foreach ($this->tickets as $player => $ticket) {
          $sender->sendMessage($this->translate('admin.report-list-line', ['player' => $player, 'report' => $ticket]));
        }
      } else if ($action === 'reply') {
        if (count($args) < 2) return self::RESULT_USAGE;
        $player = SteinsPlayer::getPlayerByName(array_shift($args));
        if (!($player instanceof SteinsPlayer) || !(isset($this->tickets[strtolower($player->getCurrentName())]))) return self::RESULT_PLAYER_NOT_FOUND;
        $this->broadcastSubscribers('admin.report-reply-success', ['player' => $sender instanceof SteinsPlayer? $sender->getCurrentName(): $sender->getName(), 'target' => $player->getCurrentName(), 'reply' => $reply = implode(" ", $args)], ['steinscore.admin.report']);
        $player->sendLocalizedMessage('admin.report-reply', ['player' => $sender instanceof SteinsPlayer? $sender->getCurrentName(): $sender->getName(), 'reply' => $reply]);
        unset($this->tickets[$player->getLowerCaseName()]);
      }
      return self::RESULT_SUCCESS;
    }

    $sender->sendMessage($this->translate('admin.report-success'));
    $this->broadcastSubscribers('admin.report-new-ticket', ['player' => $sender->getCurrentName(), 'report' => $report = implode(" ", $args)], ['steinscore.admin', 'steinscore.admin.report']);
    $this->tickets[strtolower($sender->getCurrentName())] = $report;
    return self::RESULT_SUCCESS;
  }
}
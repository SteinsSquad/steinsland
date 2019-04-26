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

namespace steinssquad\steinscore\command;


use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\CommandEnum;
use pocketmine\network\mcpe\protocol\types\CommandParameter;
use pocketmine\Server;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\steinscore\utils\Translator;


abstract class CustomCommand extends Command {

  public const RESULT_USAGE  = -1;
  public const RESULT_NO_RIGHTS = 0;
  public const RESULT_IN_GAME = 1;
  public const RESULT_PLAYER_NOT_FOUND = 2;
  public const RESULT_NOT_FOR_CREATIVE = 3;
  public const RESULT_PLAYER_IGNORES_YOU = 4;
  public const RESULT_PLAYER_HAS_IMMUNITY = 5;
  public const RESULT_NOT_ENOUGH_MONEY = 6;
  public const RESULT_SUCCESS = 7;

  public const SHORT_TYPE_NAMES = [
    'int' => 0x01,
    'float' => 0x02,
    'value' => 0x03,
    'wildcard_int' => 0x04,
    'operator' => 0x05,
    'player' => 0x06,
    'string' => 0x1b,
    'position' => 0x1d,
    'message' => 0x20,
    'rawtext' => 0x22,
    'json' => 0x25,
    'command' => 0x2c
  ];

  public static function build(array ...$data): array {
    $params = [];
    foreach ($data as $i => $param) {
      $params[$i] = new CommandParameter();
      $params[$i]->paramName = $param['name'] ?? 'args';
      $params[$i]->paramType = (isset($param['enum']) ? AvailableCommandsPacket::ARG_FLAG_ENUM : 0) |
        AvailableCommandsPacket::ARG_FLAG_VALID | self::SHORT_TYPE_NAMES[$param['type'] ?? 'rawtext'];
      $params[$i]->isOptional = $param['optional'] ?? false;
      if (isset($param['enum'])) {
        $params[$i]->enum = new CommandEnum();
        $params[$i]->enum->enumName = $param['enum']['name'] ?? $param['name'] ?? 'args';
        $params[$i]->enum->enumValues = $param['enum']['values'] ?? [];
      }
    }
    return $params;
  }

  private $module;

  private $overloads = array();
  private $usages = [];
  private $currentSender;

  public function __construct(string $name, string $module, string $permission, array $aliases = []) {
    parent::__construct($name, "$module.$name-description", null, $aliases);
    $this->module = $module;
    $this->setPermission($permission);
  }

  public function registerOverload(array ...$params) {
    $this->overloads[] = ['params' => self::build(...$params), 'permissions' => []];
    $this->usages[] = [
      'usage' => implode(" ", array_map(function (array $param) {
        $brackets = isset($param['optional']) && $param['optional'] ? ['[', ']'] : ['<', '>'];
        return $brackets[0] . (
          isset($param['enum']) && count($param['enum']['values']) <= 3 ?
            implode(":", $param['enum']['values']) : (isset($param['enum']) ? $param['enum']['name'] : $param['name'])
          ) . $brackets[1];
      }, $params)),
      'permissions' => []
    ];
  }

  public function registerPermissibleOverload(array $permissions, array ...$params) {
    $this->overloads[] = ['params' => self::build(...$params), 'permissions' => $permissions];
    $this->usages[] = [
      'usage' => implode(" ", array_map(function (array $param) {
        $brackets = isset($param['optional']) && $param['optional'] ? ['[', ']'] : ['<', '>'];
        return $brackets[0] . (
          isset($param['enum']) && count($param['enum']['values']) <= 3 ?
            ":" . implode(";", $param['enum']['values']) : $param['name']
          ) . $brackets[1];
      }, $params)),
      'permissions' => $permissions
    ];
  }

  public function getOverloads(SteinsPlayer $player): array {
    $ret = [];
    foreach ($this->overloads as $i => $data) {
      $hasPermission = count($data['permissions']) === 0;
      if (!$hasPermission) {
        foreach ($data['permissions'] as $permission)
          if ($player->hasPermission($permission)) $hasPermission = true;
      }
      if ($hasPermission) $ret[$i] = $data['params'];
    }
    return $ret;
  }

  public function getUsages() {
    return $this->usages;
  }

  public function execute(CommandSender $sender, string $commandLabel, array $args) {
    $this->currentSender = $sender;
    if (!$this->testPermissionSilent($sender)) {
      $sender->sendMessage($this->translate('generic.no-rights'));
      return false;
    }
    $result = $this->onCommand($sender, $args);
    if (is_bool($result) || is_int($result)) {
      if ($result === false || $result === self::RESULT_USAGE) {
        foreach ($this->usages as $usage) {
          $hasPermission = count($usage['permissions']) === 0;
          foreach ($usage['permissions'] as $permission) {
            if ($sender->hasPermission($permission)) $hasPermission = true;
          }
          if ($hasPermission) $sender->sendMessage($this->generic('usage', ['command' => implode(" ", [$commandLabel, $usage['usage']])]));
        }
      } else if ($result === self::RESULT_NO_RIGHTS) {
        $sender->sendMessage($this->generic('no-rights'));
      } else if ($result === self::RESULT_IN_GAME) {
        $sender->sendMessage($this->generic('in-game'));
      } else if ($result === self::RESULT_PLAYER_NOT_FOUND) {
        $sender->sendMessage($this->generic('player-not-found'));
      } else if ($result === self::RESULT_NOT_FOR_CREATIVE) {
        $sender->sendMessage($this->generic('not-for-creative'));
      } else if ($result === self::RESULT_PLAYER_IGNORES_YOU) {
        $sender->sendMessage($this->generic('player-ignores-you'));
      } else if ($result === self::RESULT_PLAYER_HAS_IMMUNITY) {
        $sender->sendMessage($this->generic('player-has-immunity'));
      } else if ($result === self::RESULT_NOT_ENOUGH_MONEY) {
        $sender->sendMessage($this->generic('not-enough-money'));
      }
    }
    unset($this->currentSender);
    return true;
  }

  abstract public function onCommand(CommandSender $sender, array $args);

  public function translate(string $message, array $args = array()) {
    return Translator::translate($this->currentSender, $message, $args);
  }

  public function module(string $message, array $args = []) {
    return Translator::translate($this->currentSender, "$this->module.$message", $args);
  }

  public function generic(string $message, array $args = []) {
    return Translator::translate($this->currentSender, "generic.$message", $args);
  }

  public function broadcast(string $messageID, array $args = [], array $targets = []) {
    if (count($targets) === 0) {
      $targets = Server::getInstance()->getOnlinePlayers();
      $targets[] = new ConsoleCommandSender();
    }
    foreach ($targets as $target) {
      $target->sendMessage(Translator::translate($target, $messageID, $args));
    }
  }

  public function broadcastSubscribers(string $messageID, array $args = [], array $permissions = []) {
    $targets = Server::getInstance()->getOnlinePlayers();
    $targets[] = new ConsoleCommandSender();
    foreach ($targets as $target) {
      foreach ($permissions as $permission) {
        if ($target->hasPermission($permission)) {
          $target->sendMessage(Translator::translate($target, $messageID, $args));
          break;
        }
      }
    }
  }

  protected function getRelativeDouble(float $original, string $input, float $min = -30000000, float $max = 30000000): float {
    if ($input{0} === "~") {
      $value = $this->getDouble(substr($input, 1));
      return $original + $value;
    }
    return $this->getDouble($input, $min, $max);
  }

  protected function getDouble($value, float $min = -30000000, float $max = 30000000): float {
    $i = (double)$value;
    if ($i < $min) {
      $i = $min;
    } elseif ($i > $max) {
      $i = $max;
    }
    return $i;
  }
}
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

namespace steinssquad\steinscore\utils;


use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use steinssquad\steinscore\Loader;


class Translator {

  public static $fallbackLanguage = 'rus';

  const LANGUAGE_LIST = [
    //'en_US' => 'eng',
    //'en_GB' => 'eng',
    'ru_RU' => 'rus',
    'uk_UA' => 'ukr',
  ];

  private static $translations = [];

  public static function registerLanguages(string $module, string $languageDir) {
    $cnt = -1;
    foreach (scandir($languageDir) as $languageFile) {
      if (preg_match("#^messages-([a-z-]+)\.yml$#i", $languageFile, $matches) > 0) {
        $fields = yaml_parse_file($languageDir . $languageFile);
        foreach ($fields as $k => $v) self::$translations[$matches[1]]["$module.$k"] = $v;
        if ($cnt !== -1 && count($fields) !== $cnt) {
          echo Server::getInstance()->getLogger()->error("[MultiLanguage] $module имеет различное кол-во ключей.");
        }
        $cnt = count($fields);
      }
    }
  }

  public static function messageExists($language, string $messageID) {
    return isset(self::$translations[Translator::getLanguage($language)][$messageID]);
  }

  public static function translate($language, string $messageID, array $args = array()) {
    $lang = self::getLanguage($language);
    if (!self::messageExists($lang, $messageID)) {
      self::notifyTranslator($lang, $messageID);
      return $messageID;
    }
    $message = self::$translations[$lang][$messageID];
    if (is_string($message)) {
      if (count($args) > 0) {
        foreach ($args as $k => $v) {
          if (isset($v{0}) && $v{0} === '%') $v = self::translate($lang, substr($v, 1));
          $message = str_replace("{{$k}}", $v, $message);
        }
      }
      $message = TextFormat::colorize($message);
    }
    return $message;
  }

  public static function getLanguage($language = null) {
    $lang = self::$fallbackLanguage;
    if ($language instanceof Player && isset(self::LANGUAGE_LIST[$language->getLocale()])) {
      $lang = self::LANGUAGE_LIST[$language->getLocale()];
    } else if (is_string($language) && isset(self::$translations[$language])) {
      $lang = $language;
    } else if (is_string($language) && isset(self::LANGUAGE_LIST[$language])) {
      $lang = self::LANGUAGE_LIST[$language];
    } else if ($language === null || $language instanceof Server || $lang instanceof ConsoleCommandSender) {
      if (isset(self::$translations[Server::getInstance()->getLanguage()->getLang()])) {
        $lang = Server::getInstance()->getLanguage()->getLang();
      }
    }
    return $lang;
  }

  private static function notifyTranslator(string $lang, string $messageID) {
    echo "[Multi-language] $messageID not found in $lang" . PHP_EOL;
    $logs = array();
    if (file_exists($cfg = Loader::$instance->getDataFolder() . "errors.json")) {
      $logs = json_decode(file_get_contents($cfg), true);
    }
    if (!isset($logs[$lang])) {
      $logs[$lang] = [];
    }
    if (array_search($messageID, $logs[$lang]) === false) {
      $logs[$lang][] = $messageID;
    }
    file_put_contents($cfg, json_encode($logs));
  }
}
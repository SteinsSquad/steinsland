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

namespace steinssquad\steinscore;


use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\TextFormat;
use steinssquad\steinscore\listener\GenericListener;
use steinssquad\steinscore\task\BroadcastTask;
use steinssquad\steinscore\task\AutoRestartTask;
use steinssquad\steinscore\utils\Translator;
use steinssquad\tests\TestFactory;


class Loader extends PluginBase {

  public const TEST_BUILD = false;
  public const PROD_BUILD = !self::TEST_BUILD;


  /** @var Loader */
  public static $instance;

  public function onLoad() {
    Translator::registerLanguages('generic', $this->getFile() . 'resources/languages/');
  }

  public function onEnable(): void {
    self::$instance = $this;

    $this->saveResource('config.yml', self::TEST_BUILD);

    $this->getServer()->getPluginManager()->registerEvents(new GenericListener(), $this);

    $this->getScheduler()->scheduleDelayedTask(new AutoRestartTask(), 20 * 60 * 45);
    $this->getScheduler()->scheduleRepeatingTask(new BroadcastTask(), 20 * 60 * 2);

    $this->getServer()->getNetwork()->setName(TextFormat::colorize($this->getConfig()->get('motd')));

    $this->getLogger()->info(TF::AQUA . 'К работе все готово.');
    if (self::TEST_BUILD) {
      $this->getLogger()->notice('Внимание. Это разрабатываемый билд!');
      TestFactory::start();
    }
  }


}
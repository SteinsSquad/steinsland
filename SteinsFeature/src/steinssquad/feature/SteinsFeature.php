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

namespace steinssquad\feature;


use pocketmine\block\Wood;
use pocketmine\block\Wood2;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\entity\projectile\Arrow;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\IPlayer;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\GoldenAppleEnchanted;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use steinssquad\feature\command\HackCommand;
use steinssquad\feature\command\helpful\CommandsCommand;
use steinssquad\feature\command\helpful\DayCommand;
use steinssquad\feature\command\helpful\ExpFlyCommand;
use steinssquad\feature\command\helpful\ExtinguishCommand;
use steinssquad\feature\command\helpful\FeedCommand;
use steinssquad\feature\command\helpful\FlyCommand;
use steinssquad\feature\command\helpful\GamemodeCommand;
use steinssquad\feature\command\helpful\GodCommand;
use steinssquad\feature\command\helpful\HealCommand;
use steinssquad\feature\command\helpful\IgnoreCommand;
use steinssquad\feature\command\helpful\ListCommand;
use steinssquad\feature\command\helpful\MilkCommand;
use steinssquad\feature\command\helpful\NearCommand;
use steinssquad\feature\command\helpful\NightCommand;
use steinssquad\feature\command\helpful\PingCommand;
use steinssquad\feature\command\helpful\RefCommand;
use steinssquad\feature\command\helpful\SpeedCommand;
use steinssquad\feature\command\hologram\AddHologramCommand;
use steinssquad\feature\command\hologram\DelHologramCommand;
use steinssquad\feature\command\hologram\HologramsCommand;
use steinssquad\feature\command\inventory\ClearCommand;
use steinssquad\feature\command\inventory\CookCommand;
use steinssquad\feature\command\inventory\DupeCommand;
use steinssquad\feature\command\inventory\EnchantCommand;
use steinssquad\feature\command\inventory\EnderChestCommand;
use steinssquad\feature\command\inventory\GiveCommand;
use steinssquad\feature\command\inventory\KitCommand;
use steinssquad\feature\command\inventory\RainbowCommand;
use steinssquad\feature\command\inventory\RepairCommand;
use steinssquad\feature\command\inventory\StackCommand;
use steinssquad\feature\command\MarryCommand;
use steinssquad\feature\command\SayCommand;
use steinssquad\feature\command\SizeCommand;
use steinssquad\feature\command\SkinCommand;
use steinssquad\feature\command\SleepCommand;
use steinssquad\feature\command\TellCommand;
use steinssquad\feature\command\troll\BurnCommand;
use steinssquad\feature\command\troll\FairyCommand;
use steinssquad\feature\command\troll\HailCommand;
use steinssquad\feature\command\troll\ScreamCommand;
use steinssquad\feature\command\troll\ShockCommand;
use steinssquad\feature\command\troll\SitCommand;
use steinssquad\feature\command\VapeCommand;
use steinssquad\feature\task\AutoMineTask;
use steinssquad\feature\task\ItemCleanerNotifyTask;
use steinssquad\feature\task\QuizTask;
use steinssquad\region\SteinsRegion;
use steinssquad\steinscore\entity\Hologram;
use steinssquad\steinscore\player\SteinsPlayer;
use steinssquad\steinscore\utils\GlobalSettings;
use steinssquad\steinscore\utils\Storage;
use steinssquad\steinscore\utils\Translator;


class SteinsFeature extends PluginBase implements Listener {

  /** @var SteinsFeature */
  public static $instance;

  public static $quizResult = null;

  /** @var Storage */
  private $holograms;
  /** @var Storage */
  private $storage;

  private $marryRequests = [];

  private $min = [];
  private $max = [];

  private $kits = [];

  public function onLoad() {
    Translator::registerLanguages('feature', $this->getFile() . 'resources/languages/');
  }

  public function onEnable() {
    self::$instance = $this;

    foreach (GlobalSettings::get('holograms') as $hologramData) {
      $hologram = new Hologram($hologramData['position'], $hologramData['title'], $hologramData['texts']);
      $hologram->spawnToAll();
    }

    $this->holograms = new Storage($this->getDataFolder() . 'holograms.db', ['holograms' => []]);
    foreach ($this->holograms->find() as $creator => $holograms) {
      foreach ($holograms['holograms'] as $position => $hologram) {
        $position = explode(":", $position);
        $position = new Position((float)$position[0], (float)$position[1], (float)$position[2], Server::getInstance()->getLevelByName($position[3]));

        $hologram = new Hologram($position, $hologram, '', $creator);
        $hologram->spawnToAll();
      }
    }

    $this->storage = new Storage($this->getDataFolder() . 'refs.db', ['ref' => null, 'kits' => [], 'marry' => null]);
    $this->kits = [
      'start' => [
        'items' => [
          ItemIds::LEATHER_TUNIC => 1,
          ItemIds::LEATHER_LEGGINGS => 1,
          ItemIds::APPLE => 32,
          ItemIds::WOOD => 64,
          ItemIds::COBBLESTONE => 128,
          ItemIds::GLASS_PANE => 32
        ],
        'cooldown' => 3600 * 24
      ],
      'fly' => [
        'items' => [
          ItemIds::LEATHER_CAP => 1,
          ItemIds::CHAIN_CHESTPLATE => 1,
          ItemIds::CHAIN_LEGGINGS => 1,
          ItemIds::LEATHER_BOOTS => 1,
          ItemIds::COOKED_PORKCHOP => 32,
          ItemIds::WOOD => [Wood::OAK => 64, Wood::SPRUCE => 64],
          ItemIds::COBBLESTONE => 128,
          ItemIds::BRICK_BLOCK => 32,
          ItemIds::GLASS_PANE => 64,
        ],
        'cooldown' => 3600 * 12
      ],
      'vip' => [
        'items' => [
          ItemIds::CHAIN_HELMET => 1,
          ItemIds::IRON_CHESTPLATE => 1,
          ItemIds::IRON_LEGGINGS => 1,
          ItemIds::CHAIN_BOOTS => 1,
          ItemIds::IRON_INGOT => 32,
          ItemIds::COOKED_BEEF => 32,
          ItemIds::WOOD => [Wood::OAK => 64, Wood::SPRUCE => 64, Wood::BIRCH => 64, Wood::JUNGLE],
          ItemIds::COBBLESTONE => 256,
          ItemIds::BRICK_BLOCK => 64,
          ItemIds::GLASS => 32
        ],
        'cooldown' => 3600 * 8
      ],
      'supra' => [
        'items' => [
          ItemIds::IRON_HELMET => 1,
          ItemIds::DIAMOND_CHESTPLATE => 1,
          ItemIds::DIAMOND_LEGGINGS => 1,
          ItemIds::IRON_BOOTS => 1,
          ItemIds::DIAMOND => 16,
          ItemIds::COOKED_BEEF => 64,
          ItemIds::WOOD => [Wood::OAK => 64, Wood::SPRUCE => 64, Wood::BIRCH => 64, Wood::JUNGLE],
          ItemIds::WOOD2 => [Wood2::ACACIA => 64, Wood2::DARK_OAK => 64],
          ItemIds::COBBLESTONE => 256,
          ItemIds::BRICK_BLOCK => 64,
          ItemIds::GLASS => 64
        ],
        'cooldown' => 3600 * 4
      ]
    ];
    $commandMap = $this->getServer()->getCommandMap();
    foreach (['spawnpoint', 'plugins', 'reload', 'transferserver', 'enchant', 'give', 'me', 'seed', 'time', 'gamemode', 'about', 'defaultgamemode', 'difficulty', 'particle', 'title', 'tell', 'say', 'list', 'effect'] as $command) $commandMap->unregister($commandMap->getCommand($command));

    $commandMap->register('steinscore/feature/helpful', new CommandsCommand());
    $commandMap->register('steinscore/feature/helpful', new DayCommand());
    $commandMap->register('steinscore/feature/helpful', new ExpFlyCommand());
    $commandMap->register('steinscore/feature/helpful', new ExtinguishCommand());
    $commandMap->register('steinscore/feature/helpful', new FeedCommand());
    $commandMap->register('steinscore/feature/helpful', new FlyCommand());
    $commandMap->register('steinscore/feature/helpful', new GamemodeCommand());
    $commandMap->register('steinscore/feature/helpful', new GodCommand());
    $commandMap->register('steinscore/feature/helpful', new HealCommand());
    $commandMap->register('steinscore/feature/helpful', new IgnoreCommand());
    $commandMap->register('steinscore/feature/helpful', new ListCommand());
    $commandMap->register('steinscore/feature/helpful', new MilkCommand());
    $commandMap->register('steinscore/feature/helpful', new NearCommand());
    $commandMap->register('steinscore/feature/helpful', new NightCommand());
    $commandMap->register('steinscore/feature/helpful', new PingCommand());
    $commandMap->register('steinscore/feature/helpful', new RefCommand());
    $commandMap->register('steinscore/feature/helpful', new SpeedCommand());
    $commandMap->register('steinscore/feature/hologram', new AddHologramCommand());
    $commandMap->register('steinscore/feature/hologram', new DelHologramCommand());
    $commandMap->register('steinscore/feature/hologram', new HologramsCommand());
    $commandMap->register('steinscore/feature/inventory', new ClearCommand());
    $commandMap->register('steinscore/feature/inventory', new CookCommand());
    $commandMap->register('steinscore/feature/inventory', new DupeCommand());
    $commandMap->register('steinscore/feature/inventory', new EnchantCommand());
    $commandMap->register('steinscore/feature/inventory', new EnderChestCommand());
    $commandMap->register('steinscore/feature/inventory', new GiveCommand());
    //$commandMap->register('steinscore/feature/inventory', new InvSeeCommand());TODO
    $commandMap->register('steinscore/feature/inventory', new KitCommand());
    $commandMap->register('steinscore/feature/inventory', new RainbowCommand());
    $commandMap->register('steinscore/feature/inventory', new RepairCommand());
    $commandMap->register('steinscore/feature/inventory', new StackCommand());
    $commandMap->register('steinscore/feature/troll', new BurnCommand());
    $commandMap->register('steinscore/feature/troll', new FairyCommand());
    $commandMap->register('steinscore/feature/troll', new HailCommand());
    $commandMap->register('steinscore/feature/troll', new ScreamCommand());
    $commandMap->register('steinscore/feature/troll', new ShockCommand());
    $commandMap->register('steinscore/feature/troll', new SitCommand());
    $commandMap->register('steinscore/feature', new HackCommand());
    //$commandMap->register('steinscore/feature', new LookAtCommand());
    $commandMap->register('steinscore/feature', new MarryCommand());
    $commandMap->register('steinscore/feature', new SayCommand());
    $commandMap->register('steinscore/feature', new SizeCommand());
    $commandMap->register('steinscore/feature', new SkinCommand());
    $commandMap->register('steinscore/feature', new SleepCommand());
    $commandMap->register('steinscore/feature', new TellCommand());
    $commandMap->register('steinscore/feature', new VapeCommand());
    $this->getServer()->getPluginManager()->registerEvents($this, $this);

    $this->getScheduler()->scheduleRepeatingTask(new ItemCleanerNotifyTask(), 20 * 60 * 2 + 15);
    $this->getScheduler()->scheduleRepeatingTask(new QuizTask(), 20 * 60 * 2.5);
    $this->getScheduler()->scheduleRepeatingTask(new AutoMineTask(), 20 * 60 * 3);


    $spawn = $this->getServer()->getDefaultLevel()->getSpawnLocation();

    $this->min = [$spawn->getFloorX() - 3000, $spawn->getFloorZ() - 3000];
    $this->max = [$spawn->getFloorX() + 3000, $spawn->getFloorZ() + 3000];

  }

  public function onDisable() {
    $this->storage->save();
    $this->holograms->save();
  }

  public function handlePlayerMoveEvent(PlayerMoveEvent $event): bool {
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();
    if ($player->hasPermission('steinscore.feature.border')) return false;
    if (
      $event->getTo()->getX() >= $this->min[0] && $event->getTo()->getX() <= $this->max[0] &&
      $event->getTo()->getZ() >= $this->min[1] && $event->getTo()->getZ() <= $this->max[1]
    ) return false;
    $event->setCancelled();
    $motion = $event->getFrom()->subtract($event->getTo());
    $motion->y = 0.35;
    $player->setMotion($motion);
    $player->sendLocalizedMessage('feature.world-border');
    return true;
  }

  /**
   * @priority HIGHEST
   * @ignoreCancelled true
   *
   * @param BlockBreakEvent $event
   * @return bool
   */
  public function handleBlockBreakEvent(BlockBreakEvent $event): bool {
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();
    if ($player->hasAchievement('diamond') || $player->hasPermission('steinscore.feature.mining')) $event->setDropsVariadic(...$player->getInventory()->addItem(...$event->getDrops()));
    return true;
  }

  public function handleEntityExplodeEvent(EntityExplodeEvent $event) {
    if ($event->getEntity() instanceof PrimedTNT) $event->setBlockList([]);
  }

  public function handlePlayerExhaustEvent(PlayerExhaustEvent $event) {
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();
    if (
      $player->hasPermission('steinscore.feature.not-starve') ||
      $player->hasAchievement('bakeCake') ||
      SteinsRegion::$instance->getFlagInside($player, 'starve', true) === false
    ) $event->setCancelled();
  }

  public function handlePlayerDeathEvent(PlayerDeathEvent $event): bool {
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();
    $event->setDeathMessage("");
    if ($player->hasPermission('steinscore.feature.keep-inventory.random')) {
      $event->setKeepInventory($player->hasPermission('steinscore.feature.keep-inventory') ? true : mt_rand(0, 1) === 1);
      if ($event->getKeepInventory()) $player->sendLocalizedMessage('feature.keep-inventory-success');
      else $player->sendLocalizedMessage('feature.keep-inventory-failed');
    }
    return true;
  }

  public function handlePlayerJoinEvent(PlayerJoinEvent $event): bool {
    $event->setJoinMessage(null);
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();
    if ($player->hasPermission('steinscore.feature.notify')) {
      /** @var SteinsPlayer $oPlayer */
      foreach (Server::getInstance()->getOnlinePlayers() as $oPlayer) $oPlayer->sendLocalizedMessage('generic.player-joined', ['player' => $player->getCurrentName(), 'group' => $player->getGroup()->getPrefix()]);
    }
    return false;
  }

  public function handlePlayerQuitEvent(PlayerQuitEvent $event) {
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();
    if ($player->hasPermission('steinscore.feature.notify')) {
      $event->setQuitMessage("");
      /** @var SteinsPlayer $oPlayer */
      foreach (Server::getInstance()->getOnlinePlayers() as $oPlayer) $oPlayer->sendLocalizedMessage('generic.player-left', ['player' => $player->getCurrentName(), 'group' => $player->getGroup()->getPrefix()]);
    }
  }

  public function handleEntityShootBowEvent(EntityShootBowEvent $event) {
    $player = $event->getEntity();
    $arrow  = $event->getProjectile();
    if ($player instanceof SteinsPlayer && $arrow instanceof Arrow) {
      if ($player->hasPermission('steinscore.feature.flame-arrow')) $arrow->setOnFire(intdiv($arrow->getFireTicks(), 20) + 100);
    }
  }

  public function handlePlayerItemConsumeEvent(PlayerItemConsumeEvent $event) {
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();
    if ($event->getItem() instanceof GoldenAppleEnchanted && !($player->hasPermission('steinscore.feature.golden-apple'))) {
      $event->setCancelled();
    }
  }

  public function handleEntityDamageEvent(EntityDamageEvent $event): bool {
    if (!($event instanceof EntityDamageByEntityEvent)) return false;
    $attacker = $event->getDamager();
    $victim = $event->getEntity();
    if (!($attacker instanceof SteinsPlayer) || !($victim instanceof SteinsPlayer)) return false;

    if (($attacker->isCreative() || $attacker->invincible || $attacker->getAllowFlight()) && !($attacker->hasPermission('steinscore.feature.attack'))) {
      $attacker->sendLocalizedPopup('feature.attack-failed');
      $event->setCancelled();
    }
    return true;
  }

  public function handleSignChangeEvent(SignChangeEvent $event) {
    if (!($event->getPlayer()->hasPermission('steinscore.feature.colors'))) return false;
    foreach ($event->getLines() as $line => $text) $event->setLine($line, TextFormat::colorize($text));
    return true;
  }

  public function handlePlayerChatEvent(PlayerChatEvent $event) {
    /** @var SteinsPlayer $player */
    $player = $event->getPlayer();
    if (self::$quizResult !== null && $event->getMessage() === strval(self::$quizResult)) {
      $player->addMoney(1000);
      self::$quizResult = null;
      SteinsPlayer::broadcast('feature.quiz-win', ['player' => $player->getCurrentName()]);
      $event->setCancelled();
    }
  }

  public function getHolograms(IPlayer $player): ?array {
    if (!($this->holograms->exists(strtolower($player->getName())))) return null;
    return $this->holograms->findByID(strtolower($player->getName()))['holograms'];
  }

  public function addHologram(SteinsPlayer $player, string $text) {
    $pos = $player->asPosition();
    $pos->x = round($pos->x, 2);
    $pos->y = round($pos->y, 2);
    $pos->z = round($pos->z, 2);
    if (!($this->holograms->exists($player->getLowerCaseName()))) {
      $this->holograms->insert($player->getLowerCaseName(), ['holograms' => ["$pos->x:$pos->y:$pos->z:" . $pos->level->getFolderName() => $text]]);
    } else {
      $holo = $this->getHolograms($player);
      $holo["$pos->x:$pos->y:$pos->z:" . $pos->level->getFolderName()] = $text;
      $this->holograms->updateByID($player->getLowerCaseName(), ['holograms' => $holo]);
    }
    $holo = new Hologram($pos, $text, '', $player->getLowerCaseName());
    $holo->spawnToAll();
  }

  public function removeHologram(Hologram $holo) {
    $holos = $this->getHolograms($player = SteinsPlayer::getOfflinePlayerExact($holo->getCreator()));
    unset($holos["$holo->x:$holo->y:$holo->z:" . $holo->level->getFolderName()]);
    if (count($holos) === 0) {
      $this->holograms->deleteByID(strtolower($player->getName()));
    } else {
      $this->holograms->updateByID(strtolower($player->getName()), ['holograms' => $holos]);
    }
    $holo->close();
    return true;
  }

  public function isMarried(IPlayer $player): bool {
    return $this->storage->exists(strtolower($player->getName())) && $this->storage->findByID(strtolower($player->getName()))['marry'] !== null;
  }

  public function getSpouse(IPlayer $player): ?string {
    if (!($this->isMarried($player))) return null;
    return $this->storage->findByID(strtolower($player->getName()))['marry'];
  }

  public function marryPlayers(IPlayer $first, IPlayer $second): bool {
    if ($this->isMarried($first) || $this->isMarried($second)) return false;
    $this->storage->exists(strtolower($first->getName())) ?
      $this->storage->updateByID(strtolower($first->getName()), ['marry' => strtolower($second->getName())]) :
      $this->storage->insert(strtolower($first->getName()), ['marry' => strtolower($second->getName())]);
    $this->storage->exists(strtolower($second->getName())) ?
      $this->storage->updateByID(strtolower($second->getName()), ['marry' => strtolower($first->getName())]) :
      $this->storage->insert(strtolower($second->getName()), ['marry' => strtolower($first->getName())]);
    return true;
  }

  public function divorcePlayer(IPlayer $player): bool {
    if (!($this->isMarried($player))) return false;
    $this->storage->updateByID($this->getSpouse($player), ['marry' => null]);
    $this->storage->updateByID(strtolower($player->getName()), ['marry' => null]);
    return true;
  }

  public function hasMarryRequest(IPlayer $player): bool {
    return isset($this->marryRequests[strtolower($player->getName())]);
  }

  public function getMarryRequest(IPlayer $player): ?string {
    if (!($this->hasMarryRequest($player))) return null;
    return $this->marryRequests[strtolower($player->getName())];
  }

  public function setMarryRequest(IPlayer $target, IPlayer $player) {
    $this->marryRequests[strtolower($target->getName())] = $player->getName();
  }

  public function removeMarryRequest(IPlayer $player): bool {
    if (!($this->hasMarryRequest($player))) return false;
    unset($this->marryRequests[strtolower($player->getName())]);
    return true;
  }

  public function kitExists(string $kitName) {
    return isset($this->kits[strtolower($kitName)]);
  }

  public function getKits(): array {
    return array_keys($this->kits);
  }

  public function isInCooldown(IPlayer $player, string $kitName): bool {
    if (!($this->kitExists($kitName))) return true;//хз
    if (!($this->storage->exists(strtolower($player->getName())))) return false;
    return time() < $this->storage->findByID(strtolower($player->getName()))['kits'][strtolower($kitName)] ?? 0;
  }

  public function getCooldown(IPlayer $player, string $kitName): ?int {
    if (!($this->isInCooldown($player, $kitName))) return null;
    return $this->storage->findByID(strtolower($player->getName()))['kits'][strtolower($kitName)] - time();
  }

  public function giveKit(SteinsPlayer $player, string $kitName, bool $force = false): bool {
    if (!($this->kitExists($kitName))) return false;
    if (!$force && $this->isInCooldown($player, $kitName)) return false;
    if (!($force)) {
      if ($this->storage->exists($player->getLowerCaseName())) {
        $kits = $this->storage->findByID($player->getLowerCaseName())['kits'];
        $kits[strtolower($kitName)] = time() + $this->kits[strtolower($kitName)]['cooldown'] ?? 0;
        $this->storage->updateByID($player->getLowerCaseName(), ['kits' => $kits]);
      } else $this->storage->insert($player->getLowerCaseName(), ['kits' => [$kitName => time() + $this->kits[strtolower($kitName)]['cooldown'] ?? 0]]);
    }
    $items = [];
    foreach ($this->kits[strtolower($kitName)]['items'] as $itemID => $itemData) {
      if (!(is_array($itemData))) {
        $items[] = Item::get($itemID, 0, $itemData)
          ->setLore([$player->localize('feature.kit-sign', ['kit' => $kitName, 'player' => $player->getCurrentName()])]);
        continue;
      }
      foreach ($itemData as $damage => $data) {
        $i = Item::get($itemID, $damage, is_array($data) ? ($data['count'] ?? 1) : $data)
          ->setLore([$player->localize('feature.kit-sign', ['kit' => $kitName, 'player' => $player->getCurrentName()])]);
        if (is_array($data)) {
          if (isset($data['enchantments'])) foreach ($data['enchantments'] as $enchantment => $level) $i->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment($enchantment), $level));
          if (isset($data['name'])) $i->setCustomName(TextFormat::colorize(str_replace('{player}', $player->getCurrentName(), $data['name'])));
        }
        $items[] = $i;
      }
    }
    $ret = true;
    foreach ($player->getInventory()->addItem(...$items) as $drop) {
      if ($drop->getCount() > $drop->getMaxStackSize()) {
        $items = [];
        while ($drop->getCount() > 0) {
          $items[] = clone $drop->setCount($drop->getCount() >= $drop->getMaxStackSize() ? $drop->getMaxStackSize() : $drop->getCount());
          $drop->count -= $drop->getCount() >= $drop->getMaxStackSize() ? $drop->getMaxStackSize() : $drop->getCount();
        }
        $drop = $items;
      }
      foreach (is_array($drop) ? $drop : [$drop] as $item) $player->getLevel()->dropItem($player, $item, new Vector3(0, 0.3, 0));
      $ret = false;
    }
    return $ret;
  }

  public function hasActivatedRef(IPlayer $player): bool {
    return $this->storage->exists(strtolower($player->getName())) && $this->storage->findByID(strtolower($player->getName()))['ref'] !== null;
  }

  public function getActivatedRef(IPlayer $player): ?string {
    if (!$this->hasActivatedRef($player)) return null;
    return $this->storage->findByID(strtolower($player->getName()))['ref'];
  }

  public function setActivatedRef(IPlayer $player, string $ref): bool {
    if (!($this->hasActivatedRef($player)) && isset(GlobalSettings::get('refs')[$ref]) !== null) {
      if (!($this->storage->exists(strtolower($player)))) $this->storage->insert(strtolower($player->getName()), ['ref' => $ref]);
      else $this->storage->updateByID(strtolower($player->getName()), ['ref' => $ref]);
      Server::getInstance()->dispatchCommand(new ConsoleCommandSender(), str_replace('{username}', $player->getName(), GlobalSettings::get('refs')[$ref]));
    }
    return false;
  }
}
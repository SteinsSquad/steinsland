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

namespace steinssquad\steinscore\player;


use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\IPlayer;
use pocketmine\item\Armor;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\SetTitlePacket;
use pocketmine\network\mcpe\protocol\types\CommandData;
use pocketmine\network\mcpe\protocol\types\CommandEnum;
use pocketmine\network\mcpe\protocol\types\CommandParameter;
use pocketmine\OfflinePlayer;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Color;
use pocketmine\utils\TextFormat;
use steinssquad\economy\SteinsEconomy;
use steinssquad\perms\model\Group;
use steinssquad\perms\SteinsPerms;
use steinssquad\region\SteinsRegion;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\entity\Pet;
use steinssquad\steinscore\utils\AchievementList;
use steinssquad\steinscore\utils\ParseUtils;
use steinssquad\steinscore\utils\Translator;


class SteinsPlayer extends Player {

  public const AD_PATTERN = '/[-a-zа-яё0-9@:%_\\+.~#?&\/=]{2,256}\.([rpр][uуy]|[tт][kк]|l[aа]nd|n[eе][tт]|[cс][oо]m|u[aа]|su|[хx]{3}|[рp]ф|[oо]rg|i[oо]|[pр]r[oо]|m[eе]|[рp]w|[тt][oо][pр]|ml|[pр][eе]|[xх][yу]z|g[аa]|fun)/i';
  public const IP_PATTERN = '/(?:\\d{1,3}[.,\\-:;\\/()=?}+ ]{1,4}){3}\\d{1,3}/';

  public static function getPlayerByName(string $name): ?SteinsPlayer {
    /** @var SteinsPlayer $player */
    $player = Server::getInstance()->getPlayer($name);
    if ($player instanceof SteinsPlayer) return $player;
    $found = null;
    $name = strtolower($name);
    $delta = PHP_INT_MAX;
    foreach (Server::getInstance()->getOnlinePlayers() as $player) {
      if (stripos($player->getCurrentName(), $name) === 0) {
        $curDelta = strlen($player->getCurrentName()) - strlen($name);
        if ($curDelta < $delta) {
          $found = $player;
          $delta = $curDelta;
        }
        if ($curDelta === 0) break;
      }
    }
    return $found;
  }

  public static function getPlayerExact(string $name): ?SteinsPlayer {
    $name = strtolower($name);
    /** @var SteinsPlayer $player */
    $player = Server::getInstance()->getPlayerExact($name);
    if ($player instanceof SteinsPlayer) return $player;
    foreach (Server::getInstance()->getOnlinePlayers() as $player) {
      if (strtolower($player->getCurrentName()) === $name) return $player;
    }
    return null;
  }

  public static function getOfflinePlayer(string $name): ?IPlayer {
    return SteinsPlayer::getOfflinePlayerExact($name) ?? SteinsPlayer::getPlayerByName($name);
  }

  public static function getOfflinePlayerExact(string $name): ?IPlayer {
    return Server::getInstance()->hasOfflinePlayerData($name) ?
      Server::getInstance()->getOfflinePlayer($name) :
      SteinsPlayer::getPlayerExact($name);
  }

  public static function getPlayerName(IPlayer $player) {
    return $player instanceof SteinsPlayer? $player->getCurrentName() : $player->getName();
  }

  public static function broadcast(string $key, array $placeholders = []) {
    foreach (SteinsPlayer::getOnlinePlayers() as $player) {
      $player->sendLocalizedMessage($key, $placeholders);
    }
  }

  /**
   * @return SteinsPlayer[]
   */
  public static function getOnlinePlayers(): array {
    return Server::getInstance()->getOnlinePlayers();
  }

  public $invincible = false;
  public $vanish = false;
  public $job = null;

  /** @var Pet */
  private $pet;

  private $streak = 0;

  private $lastMessage = '';
  private $canMessageAgain = 0;

  private $ignoreList = [];

  private $taskList = [];

  private $group = null;
  private $money = 0;
  private $homes = [];
  /** @var Position */
  private $backPosition = null;
  private $class = null;
  private $fakeName = null;
  private $prefix = null;
  private $suffix = null;

  public function setFakeName(?string $fakeName = null) {
    $this->fakeName = $fakeName;
    $this->updateNameTag();
  }

  public function getFakeName(): ?string {
    return $this->fakeName;
  }

  public function getCurrentName(): string {
    return $this->getFakeName() ?? $this->getName();
  }


  public function canSee(Player $player): bool {
    if ($player instanceof SteinsPlayer && $player->vanish) {
      return $this->hasPermission('steinscore.admin.vanish') && $this->getGroup()->getPriority() >= $player->getGroup()->getPriority();
    }
    return parent::canSee($player);
  }

  public function getTeleportCooldown() {
    if ($this->hasPermission('steinscore.teleport.cooldown')) return 0;
    if ($this->hasPermission('steinscore.teleport.cooldown.1')) return 1;
    if ($this->hasPermission('steinscore.teleport.cooldown.3')) return 3;
    return 5;
  }

  public function initEntity(): void {
    parent::initEntity();
    $this->setMaxHealth($this->hasAchievement('streak') ? 22 : 20);
    $this->money = $this->namedtag->getLong('money', SteinsPerms::$instance->getGroup(new OfflinePlayer($this->server, $this->username))->getDefaultMoney());
    $this->homes = ParseUtils::homesFromCompound($this->namedtag->getCompoundTag('homes'));
    $this->backPosition = ParseUtils::backPositionFromCompound($this->namedtag->getCompoundTag('backPosition'));
    $this->class = $this->namedtag->getString('class', '');
    if ($this->class === '') $this->class = null;
    $this->fakeName = $this->namedtag->getString('fakeName', '');
    if ($this->fakeName === '') $this->fakeName = null;
  }

  public function save() {
    if ($this->closed) throw new \InvalidStateException("Tried to save closed player");

    $this->money === $this->getGroup()->getDefaultMoney() ? $this->namedtag->removeTag('money') : $this->namedtag->setLong('money', $this->money);
    count($this->homes) === 0 ? $this->namedtag->removeTag('homes') : $this->namedtag->setTag(ParseUtils::compoundFromHomes($this->homes));
    $this->backPosition === null || $this->backPosition->level === null ? $this->namedtag->removeTag('backPosition') : $this->namedtag->setTag(ParseUtils::compoundFromBackPosition($this->backPosition));
    $this->class === null ? $this->namedtag->removeTag('class') : $this->namedtag->setString('class', $this->class);
    $this->fakeName === null ? $this->namedtag->removeTag('fakeName') : $this->namedtag->setString('fakeName', $this->fakeName);
    $this->prefix === null ? $this->namedtag->removeTag('prefix') : $this->namedtag->setString('prefix', $this->prefix);
    $this->suffix === null ? $this->namedtag->removeTag('suffix') : $this->namedtag->setString('suffix', $this->suffix);

    parent::save();
  }

  public function onDeath(): void {
    $this->setBackPosition($this->asPosition());
    if ($this->lastDamageCause instanceof EntityDamageByEntityEvent) {
      $attacker = $this->lastDamageCause->getDamager();
      if ($attacker instanceof SteinsPlayer && $attacker !== $this) {
        $attacker->awardAchievement('firstKill');
        if (++$attacker->streak >= 5) $this->awardAchievement('streak');
        $attacker->addMoney(1000 * ($this->streak + 1) * ($this->hasPermission('steinscore.feature.kill-money') ? 2 : 1));
        $attacker->sendLocalizedMessage('generic.you-killed', ['player' => $this->getCurrentName()]);
        SteinsEconomy::$instance->completeOrder($this, $attacker);
        $this->streak = 0;
        $this->sendLocalizedMessage('generic.you-were-killed', ['player' => $attacker->getCurrentName()]);
      }
    }
    parent::onDeath();
  }

  public function getPrefix(): string {
    return $this->prefix ?? $this->getGroup()->getPrefix();
  }

  public function setPrefix(?string $prefix = null) {
    $this->prefix = $prefix;
    $this->updateNameTag();
  }

  public function getSuffix(): string {
    return $this->suffix ?? $this->getGroup()->getSuffix();
  }

  public function setSuffix(?string $suffix = null) {
    $this->suffix = $suffix;
    $this->updateNameTag();
  }

  public function getBackPosition(): Position {
    return $this->backPosition;
  }

  public function setBackPosition(Position $position): bool {
    $this->backPosition = $position;
    return true;
  }

  public function getHomes(): array {
    return $this->homes;
  }

  public function homeExists(string $home): bool {
    return isset($this->homes[strtolower($home)]);
  }

  public function setHome(string $home, Position $position = null): bool {
    $this->homes[strtolower($home)] = $position ?? $this->asPosition();
    return true;
  }

  public function deleteHome(string $home): bool {
    if (!$this->homeExists($home)) return false;
    unset($this->homes[strtolower($home)]);
    return true;
  }

  public function getGroup(): Group {
    return $this->group ?? SteinsPerms::$instance->getDefaultGroup();
  }

  public function isGroupTemporary(): bool {
    return SteinsPerms::$instance->getGroupUntil($this) !== null;
  }

  public function setGroup(Group $group, int $until = null): bool {
    return SteinsPerms::$instance->setGroup($this, $group, $until);
  }

  public function changeGroup(Group $group) {
    $this->group = $group;
    $this->updateNameTag();
  }

  public function updateNameTag() {
    $this->setNameTag($this->getGroup()->getNametagFormat(
      [
        'username' => $this->getCurrentName(),
        'prefix' => $this->prefix ?? $this->getGroup()->getPrefix(),
        'suffix' => $this->suffix ?? $this->getGroup()->getSuffix()
      ]
    ));
  }

  public function getMoney(): int {
    return $this->money;
  }

  public function hasMoney(int $amount): bool {
    return $this->money >= $amount;
  }

  public function setMoney(int $amount): bool {
    $this->money = $amount;
    return true;
  }

  public function addMoney(int $amount): bool {
    if ($amount < 0) return false;
    $this->money += $amount;
    return true;
  }

  public function reduceMoney(int $amount): bool {
    if ($amount > $this->money || $amount < 0) return false;
    $this->money -= $amount;
    return true;
  }

  public function localize(string $message, array $args = []): string {
    $ret = Translator::translate($this, $message, $args);
    if (is_array($ret)) {
      $ret = $ret[array_rand($ret)];
      if (count($args) > 0) {
        foreach ($args as $k => $v) {
          if (isset($v{0}) && $v{0} === '%') $v = Translator::translate($this, substr($v, 1));
          $ret = str_replace("{{$k}}", $v, $ret);
        }
      }
      $ret = TextFormat::colorize($ret);
    }
    return $ret;
  }

  public function sendLocalizedMessage(string $message, array $args = []): void {
    $this->sendMessage($this->localize($message, $args));
  }

  public function sendLocalizedPopup(string $message, array $args = []): void {
    $this->sendPopup($this->localize($message, $args));
  }

  public function sendLocalizedTip(string $message, array $args = []): void {
    $this->sendPopup($this->localize($message, $args));
  }

  public function sendLocalizedTitle(string $message, array $args = []): void {
    $this->sendTitleText($this->localize($message, $args), SetTitlePacket::TYPE_SET_TITLE);
  }

  public function sendLocalizedSubTitle(string $message, array $args = []): void {
    $this->addSubTitle($this->localize($message, $args));
  }

  public function sendLocalizedActionBar(string $message, array $args = []): void {
    $this->addActionBarMessage($this->localize($message, $args));
  }

  public function attack(EntityDamageEvent $source): void {
    if ($source instanceof EntityDamageByEntityEvent) {
      $player = $source->getDamager();
      if ($player instanceof SteinsPlayer) {
        if ($this->pet instanceof Pet) $this->pet->setTarget($player);
      }
    }
    if ($source->getCause() === $source::CAUSE_LAVA && $this->hasPermission('steinscore.feature.fire-proof')) $source->setCancelled();
    if ($this->invincible) $source->setCancelled();
    parent::attack($source);
  }

  public function fall(float $fallDistance): void {
    if (!($this->hasPermission('steinscore.feature.unfallable'))) parent::fall($fallDistance);
  }

  public function canBreathe(): bool {
    if (SteinsRegion::$instance->getFlagInside($this, 'breathe', true) === false && !$this->hasPermission('steinscore.region')) {
      foreach (SteinsRegion::$instance->getRegionsInside($this) as $region) {
        if (SteinsRegion::$instance->hasRegionPermission($region, $this, SteinsRegion::PERMISSION_NOT_MEMBER)) return false;
      }
      return true;
    }
    return $this->hasPermission('steinscore.feature.waterproof') || parent::canBreathe();
  }

  public function awardAchievement(string $achievementId): bool {
    if (isset(AchievementList::MAP[$achievementId]) && !$this->hasAchievement($achievementId)) {
      foreach (AchievementList::MAP[$achievementId] as $require) {
        if (!$this->hasAchievement($require)) return false;
      }
      $this->sendLocalizedMessage('feature.achievements-earned', ['achievement' => "%feature.achievements-$achievementId"]);
      if ($achievementId === 'diamond') $this->sendLocalizedMessage('feature.achievements-builder-branch-complete');
      if ($achievementId === 'bakeCake') $this->sendLocalizedMessage('feature.achievements-farmer-branch-complete');
      if ($achievementId === 'streak') {
        $this->sendLocalizedMessage('feature.achievements-hunter-branch-complete');
        $this->setMaxHealth(22);
      }
      $this->achievements[$achievementId] = true;
      return true;
    }
    return false;
  }

  public function hasAchievement(string $achievementId): bool {
    if (!isset(AchievementList::MAP[$achievementId])) return false;
    return $this->achievements[$achievementId] ?? false;
  }

  public function chatPreprocess(PlayerChatEvent $event): bool {
    if (!($this->chatSpamProcess($event))) return false;
    $this->lastMessage = $event->getMessage();
    $this->canMessageAgain = time() + 3;
    //if (Loader::getSettings()->get('local-chat', false)) {
    //  if (substr($event->getMessage(), 0, 1) !== '!')
    //    $event->setRecipients(array_filter($event->getRecipients(), function ($player) {
    //      return !$player instanceof SteinsPlayer || $player->distance($this) <= Loader::getSettings()->get('local-chat-radius');
    //    }));
    //  else $event->setMessage(substr($event->getMessage(), 1));
    //}
    $event->setFormat($this->getGroup()->getChatFormat(
      [
        'username' => $this->getCurrentName(),
        'message' => $this->hasPermission('steinscore.feature.colors') ? $event->getMessage() : TextFormat::clean(TextFormat::colorize($event->getMessage()), true),
        'prefix' => $this->prefix ?? $this->getGroup()->getPrefix(),
        'suffix' => $this->suffix ?? $this->getGroup()->getSuffix()
      ]
    ));
    return true;
  }


  public function chatSpamProcess(PlayerChatEvent $event) {
    if ($this->hasPermission('steinscore.feature.spam')) return true;
    if ($this->lastMessage === $event->getMessage()) {
      $this->sendLocalizedMessage('generic.spam-detected');
      $event->setCancelled();
    } else if ($this->canMessageAgain > time()) {
      $this->sendLocalizedMessage('generic.spam-too-often', ['seconds' => $this->canMessageAgain - time()]);
      $event->setCancelled();
    } else if (preg_match(self::AD_PATTERN, $event->getMessage()) > 0 || preg_match(self::IP_PATTERN, $event->getMessage()) > 0) {
      $this->sendLocalizedMessage('generic.ad-detected');
      $event->setCancelled();
    }
    return !$event->isCancelled();
  }

  public function sendCommandData() {
    $pk = new AvailableCommandsPacket();
    foreach ($this->server->getCommandMap()->getCommands() as $name => $command) {
      if (isset($pk->commandData[$command->getName()]) || $command->getName() === "help" || !$command->testPermissionSilent($this)) {
        continue;
      }
      $data = new CommandData();
      //TODO: commands containing uppercase letters in the name crash 1.9.0 client
      $data->commandName = strtolower($command->getName());
      $data->commandDescription = $this->server->getLanguage()->translateString($command->getDescription());
      $data->flags = 0;
      $data->permission = 0;
      if (!($command instanceof CustomCommand)) {//TODO: Удалить это, когда в PMMP появится возможность создавать аргументы из коробки.
        $parameter = new CommandParameter();
        $parameter->paramName = "args";
        $parameter->paramType = AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_RAWTEXT;
        $parameter->isOptional = true;
        $data->overloads[0][0] = $parameter;
      } else {
        $data->commandDescription = $this->localize($command->getDescription());
        $data->overloads = $command->getOverloads($this);
      }
      $aliases = $command->getAliases();
      if (!empty($aliases)) {
        if (!in_array($data->commandName, $aliases, true)) {
          //work around a client bug which makes the original name not show when aliases are used
          $aliases[] = $data->commandName;
        }
        $data->aliases = new CommandEnum();
        $data->aliases->enumName = ucfirst($command->getName()) . "Aliases";
        $data->aliases->enumValues = $aliases;
      }
      $pk->commandData[$command->getName()] = $data;
    }
    $this->dataPacket($pk);
  }

  public function isIgnorePlayer(IPlayer $player): bool {
    return array_search(strtolower($player->getName()), $this->ignoreList) !== false;
  }

  public function ignorePlayer(IPlayer $player): void {
    if (!$this->isIgnorePlayer($player)) $this->ignoreList[] = strtolower($player->getName());
  }

  public function unignorePlayer(IPlayer $player): void {
    if ($this->isIgnorePlayer($player)) unset($this->ignoreList[array_search(strtolower($player->getName()), $this->ignoreList)]);
  }

  public function addTask(\Closure $task, int $seconds = 1, ...$params) {
    $this->taskList[] = ['clojure' => $task, 'time' => time() + $seconds, 'params' => $params];
  }

  public function onUpdate(int $currentTick): bool {
    $hasUpdate = parent::onUpdate($currentTick);
    foreach ($this->taskList as $taskID => $taskData) {
      if (time() >= $taskData['time']) {
        try {
          $taskData['clojure']($this, ...$taskData['params']);
        } catch (\Throwable $exception) {
          $this->server->getLogger()->logException($exception);
        }
        unset($this->taskList[$taskID]);
      }
    }
    if ($currentTick % 20 === 0) {
      $color = new Color(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
      /** @var Armor $item */
      foreach ($this->getArmorInventory()->getContents(true) as $id => $item) {
        if ($item instanceof Armor && $this->hasPermission('steinscore.feature.rainbow') && $item->getCustomName() === ' ') {
          $item->setCustomColor($color);
          $this->getArmorInventory()->setItem($id, $item);
        }
      }
    }
    return $hasUpdate;
  }
}
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

namespace steinssquad\feature\command\inventory;


use pocketmine\command\CommandSender;
use pocketmine\item\Armor;
use pocketmine\item\Bow;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Sword;
use pocketmine\item\Tool;
use steinssquad\steinscore\command\CustomCommand;
use steinssquad\steinscore\player\SteinsPlayer;


class EnchantCommand extends CustomCommand {

  public function __construct() {
    parent::__construct('enchant', 'feature', 'steinscore.feature.enchant.3;steinscore.feature.enchant.1');
  }

  public function onCommand(CommandSender $sender, array $args): int {
    if (!($sender instanceof SteinsPlayer)) return self::RESULT_IN_GAME;
    if ($sender->isCreative()) return self::RESULT_NOT_FOR_CREATIVE;
    $item = $sender->getInventory()->getItemInHand();
    if (!($item instanceof Durable) || $item->hasEnchantments()) {
      $sender->sendLocalizedMessage('feature.enchant-failed');
      return self::RESULT_SUCCESS;
    }
    $level = 1;
    if ($sender->hasPermission('steinscore.feature.enchant')) $level = 5;
    else if ($sender->hasPermission('steinscore.feature.enchant.3')) $level = 3;
    $enchantment = [Enchantment::PROTECTION];
    if ($item instanceof Armor) {
      $enchantment = [Enchantment::PROTECTION, Enchantment::FIRE_PROTECTION, Enchantment::FEATHER_FALLING, Enchantment::BLAST_PROTECTION, Enchantment::PROJECTILE_PROTECTION];
      if ($level === 5) $enchantment[] = Enchantment::THORNS;
    } else if ($item instanceof Sword) {
      $enchantment = [Enchantment::SHARPNESS, Enchantment::KNOCKBACK];
      if ($level === 5) $enchantment[] = Enchantment::FIRE_ASPECT;
    } else if ($item instanceof Bow) {
      $enchantment = [Enchantment::POWER, Enchantment::PUNCH];
      if ($level === 5) $enchantment[] = Enchantment::INFINITY;
    } else if ($item instanceof Tool) {
      $enchantment = [Enchantment::EFFICIENCY, Enchantment::SILK_TOUCH];
      if ($level === 5) $enchantment[] = Enchantment::UNBREAKING;
    }
    $item->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment($enchantment[array_rand($enchantment)]), $level));
    $sender->getInventory()->setItemInHand($item);
    $sender->sendLocalizedMessage('feature.enchant-success');
    return self::RESULT_SUCCESS;
  }
}
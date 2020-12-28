<?php

/*
 *    ____                 ____  _
 *   / ___|_ __ ___  _ __ |  _ \| |_   _ ___
 *  | |   | '__/ _ \| '_ \| |_) | | | | / __|
 *  | |___| | | (_) | |_) |  __/| | |_| \__ \
 *   \____|_|  \___/| .__/|_|   |_|\__,_|___/
 *                  |_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace TeamBixby\CropPlus;

use pocketmine\block\BlockIds;
use pocketmine\block\Sapling;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\ItemIds;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use TeamBixby\CropPlus\crop\CropManager;

class EventListener implements Listener{

	/**
	 * @param PlayerInteractEvent $event
	 *
	 * @priority        MONITOR
	 * @ignoreCancelled true
	 */
	public function onPlayerInteract(PlayerInteractEvent $event) : void{
		$block = $event->getBlock();

		if($block->getId() === BlockIds::FARMLAND && $event->getFace() === Vector3::SIDE_UP){
			$b = $event->getItem()->getBlock();
			$b->position(Position::fromObject($block->add(0, 1), $block->getLevel()));
			CropManager::getInstance()->placeCrop($b);
		}elseif($block->getId() === BlockIds::WOOD && $block->getDamage() === Sapling::JUNGLE){
			$item = $event->getItem();
			if($item->getId() === ItemIds::DYE && $item->getDamage() === 3){
				$b = $item->getBlock();
				$b->position($block->getSide($event->getFace()));
				$b->setDamage(Vector3::getOppositeSide($event->getFace()));
				CropManager::getInstance()->placeCrop($b);
			}
		}
	}

	public function onBlockBreak(BlockBreakEvent $event) : void{
		$block = $event->getBlock();

		CropManager::getInstance()->destroyCrop($block);
	}
}
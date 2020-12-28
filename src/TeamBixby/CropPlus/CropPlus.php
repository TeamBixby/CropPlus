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

use pocketmine\block\BlockFactory;
use pocketmine\item\ItemFactory;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;
use TeamBixby\CropPlus\block\CocoaBlock;
use TeamBixby\CropPlus\crop\CropManager;
use TeamBixby\CropPlus\item\CocoaSeeds;

class CropPlus extends PluginBase{
	use SingletonTrait;

	/** @var CropManager */
	protected $cropManager;

	public function onLoad() : void{
		self::setInstance($this);
	}

	public function onEnable() : void{
		$this->cropManager = new CropManager();
		$this->cropManager->loadConfig();

		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

		$this->getScheduler()->scheduleDelayedRepeatingTask(new ClosureTask(function(int $unused) : void{
			$this->cropManager->growCrops();
		}), 20, 20);

		BlockFactory::registerBlock(new CocoaBlock(), true);

		ItemFactory::registerItem(new CocoaSeeds(), true);

		//ItemFactory::registerItem($item = new Item(ItemIds::COCOA, 0, "Cocoa seeds"));
		//Item::addCreativeItem($item);
	}

	public function onDisable() : void{
		$this->cropManager->saveConfig();
	}

	public function getCropManager() : CropManager{
		return $this->cropManager;
	}

	public function getGrowInterval() : int{
		return $this->getConfig()->get("grow-interval", 60);
	}
}
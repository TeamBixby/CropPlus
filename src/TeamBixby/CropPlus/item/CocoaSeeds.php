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

namespace TeamBixby\CropPlus\item;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;

class CocoaSeeds extends Item{

	public function __construct(){
		parent::__construct(ItemIds::DYE, 3, "Cocoa seeds");
	}

	public function getBlock() : Block{
		return BlockFactory::get(BlockIds::COCOA_BLOCK);
	}
}
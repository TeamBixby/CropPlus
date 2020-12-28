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

namespace TeamBixby\CropPlus\block;

use pocketmine\block\Block;
use pocketmine\block\BlockIds;
use pocketmine\block\BlockToolType;
use pocketmine\block\Crops;
use pocketmine\block\Sapling;
use pocketmine\event\block\BlockGrowEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\Player;

use function mt_rand;

class CocoaBlock extends Crops{

	public function __construct(int $meta = 0){
		parent::__construct(BlockIds::COCOA, $meta, "Cocoa Block", ItemIds::COCOA);
		$this->meta = $meta;
	}

	public function getName() : string{
		return "Cocoa Block";
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if($item->getId() !== ItemIds::DYE){
			return false;
		}
		if($item->getDamage() !== 3){
			return false;
		}
		if($blockClicked->getId() !== BlockIds::WOOD){
			return false;
		}
		if($blockClicked->getDamage() !== Sapling::JUNGLE){
			return false;
		}
		if($face === Vector3::SIDE_UP || $face === Vector3::SIDE_DOWN){
			return false;
		}
		/*
		try{
			$meta = Vector3::getOppositeSide($face);
		}catch(Throwable $e){
			return false;
		}
		*/
		$metas = [
			Vector3::SIDE_NORTH => 0,
			Vector3::SIDE_SOUTH => 2,
			Vector3::SIDE_WEST => 3,
			Vector3::SIDE_EAST => 1
		];
		$meta = $metas[$face] ?? -1;

		if($meta === -1){
			return false;
		}

		$this->meta = $meta;

		$this->getLevelNonNull()->setBlock($blockReplace, $this, true, true);
		return true;
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if(mt_rand(0, 2) === 1){
			if($this->meta < 11 && $this->getNextMetaFor() !== -1){
				$block = clone $this;
				$this->meta += 4;
				$ev = new BlockGrowEvent($this, $block);
				$ev->call();
				if(!$ev->isCancelled()){
					$this->getLevelNonNull()->setBlock($this, $ev->getNewState(), true, true);
				}
			}
		}
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}

	public function getHardness() : float{
		return 0.2;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_AXE;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			ItemFactory::get(ItemIds::DYE, 3, ($this->meta >> 2) === 2 ? mt_rand(2, 3) : 1)
		];
	}

	public function getPickedItem() : Item{
		return ItemFactory::get(ItemIds::DYE, 3);
	}

	public function onNearbyBlockChange() : void{
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		if($this->getNextMetaFor() !== -1 && $item->getId() === ItemIds::DYE && $item->getDamage() === 15){
			$block = clone $this;
			$block->meta += 4;
			if($block->meta > 11){
				$block->meta = 11;
			}

			$ev = new BlockGrowEvent($this, $block);
			$ev->call();
			if(!$ev->isCancelled()){
				$this->getLevelNonNull()->setBlock($this, $ev->getNewState(), true, true);
			}

			$item->pop();

			return true;
		}

		return false;
	}

	public function getNextMetaFor() : int{
		switch($this->meta){
			case 0:
				return 4;
			case 1:
				return 5;
			case 2:
				return 6;
			case 3:
				return 7;
			case 4:
				return 8;
			case 5:
				return 9;
			case 6:
				return 10;
			case 7:
				return 11;
			default:
				return -1;
		}
	}
}
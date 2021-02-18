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

namespace TeamBixby\CropPlus\crop;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\event\block\BlockGrowEvent;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\utils\SingletonTrait;
use TeamBixby\CropPlus\block\CocoaBlock;
use TeamBixby\CropPlus\CropPlus;
use TeamBixby\CropPlus\util\CropUtils;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function time;
use function yaml_emit;
use function yaml_parse;

class CropManager{
	use SingletonTrait;

	/** @var array */
	protected $data = [];

	public function __construct(){
		self::setInstance($this);
	}

	public function loadConfig() : void{
		$plugin = CropPlus::getInstance();
		$plugin->saveDefaultConfig();

		if(file_exists($file = $plugin->getDataFolder() . "crops.yml")){
			$this->data = yaml_parse(file_get_contents($file));
		}
	}

	public function saveConfig() : void{
		file_put_contents(CropPlus::getInstance()->getDataFolder() . "crops.yml", yaml_emit($this->data));
	}

	public function growCrops() : void{
		$normalCount = 0;
		$stemCount = 0;
		$cocoaCount = 0;
		foreach($this->data as $key => $data){
			switch($data["type"]){
				case CropIds::PUMPKIN:
				case CropIds::WATERMELON:
					if($this->growStem(CropUtils::str2pos($key))){
						$stemCount++;
					}
					break;
				case CropIds::WHEAT:
				case CropIds::CARROT:
				case CropIds::POTATO:
					if($this->growNormal(CropUtils::str2pos($key))){
						$normalCount++;
					}
					break;
				case CropIds::COCOA:
					if($this->growCocoa(CropUtils::str2pos($key))){
						$cocoaCount++;
					}
			}
		}
		CropPlus::getInstance()->getLogger()->debug("Grow result: normal($normalCount), stem($stemCount), cocoa($cocoaCount)");
	}

	public function growNormal(Position $pos, bool $force = false) : bool{
		if(!$pos->isValid()){
			return false;
		}
		if(($down = $pos->getLevel()->getBlock($pos->getSide(Vector3::SIDE_DOWN)))->getId() !== BlockIds::FARMLAND)
		{
			$this->destroyCrop($down);
			return false;
		}
		$key = CropUtils::pos2str($pos);
		if(!isset($this->data[$key])){
			return false;
		}
		if(!$force && time() - $this->data[$key]["updatedAt"] < CropPlus::getInstance()->getGrowInterval()){
			return false;
		}
		if(!$pos->getLevel()->isChunkLoaded($pos->getFloorX() >> 4, $pos->getFloorZ() >> 4)){
			$pos->getLevel()->loadChunk($pos->getFloorX() >> 4, $pos->getFloorZ() >> 4);
		}
		++$this->data[$key]["age"];
		$block = BlockFactory::get($this->data[$key]["block"], $this->data[$key]["age"]);
		$ev = new BlockGrowEvent($pos->getLevel()->getBlock($pos), $block);
		$ev->call();
		if(!$ev->isCancelled()){
			$pos->getLevel()->setBlock($pos, $block);
		}
		$this->data[$key]["updatedAt"] = time();
		if($this->data[$key]["age"] >= 7){
			unset($this->data[$key]);
		}
		return true;
	}

	public function growStem(Position $pos, bool $force = false) : bool{
		if(!$pos->isValid()){
			return false;
		}
		if(($down = $pos->getLevel()->getBlock($pos->getSide(Vector3::SIDE_DOWN)))->getId() !== BlockIds::FARMLAND)
		{
			$this->destroyCrop($down);
			return false;
		}
		$key = CropUtils::pos2str($pos);
		if(!isset($this->data[$key])){
			return false;
		}
		if(!$force && time() - $this->data[$key]["updatedAt"] < CropPlus::getInstance()->getGrowInterval()){
			return false;
		}
		if(!$pos->getLevel()->isChunkLoaded($pos->getFloorX() >> 4, $pos->getFloorZ() >> 4)){
			$pos->getLevel()->loadChunk($pos->getFloorX() >> 4, $pos->getFloorZ() >> 4);
		}
		if($this->data[$key]["age"] < 7){
			++$this->data[$key]["age"];
			$block = BlockFactory::get($this->data[$key]["stem"], $this->data[$key]["age"]);
			$ev = new BlockGrowEvent($pos->getLevel()->getBlock($pos), $block);
			$ev->call();
			if(!$ev->isCancelled()){
				$pos->getLevel()->setBlock($pos, $block);
			}
		}else{
			$side = $this->data[$key]["side"];
			if(($before = $pos->getLevel()->getBlock($pos->getSide($side)))->getId() !== BlockIds::AIR){
				return false;
			}
			$block = BlockFactory::get($this->data[$key]["block"]);
			$ev = new BlockGrowEvent($before, $block);
			$ev->call();
			if(!$ev->isCancelled()){
				$pos->getLevel()->setBlock($pos->getSide($side), $block);
			}
		}
		$this->data[$key]["updatedAt"] = time();
		return true;
	}

	public function growCocoa(Position $pos, bool $force = false) : bool{
		if(!$pos->isValid()){
			return false;
		}
		$key = CropUtils::pos2str($pos);
		if(!isset($this->data[$key])){
			return false;
		}
		if(!$force && time() - $this->data[$key]["updatedAt"] < CropPlus::getInstance()->getGrowInterval()){
			return false;
		}
		if(!$pos->getLevel()->isChunkLoaded($pos->getFloorX() >> 4, $pos->getFloorZ() >> 4)){
			$pos->getLevel()->loadChunk($pos->getFloorX() >> 4, $pos->getFloorZ() >> 4);
		}

		$block = $pos->getLevel()->getBlock($pos);
		if(!$block instanceof CocoaBlock){
			return false;
		}
		$next = $block->getNextMetaFor();
		if($next === -1){
			unset($this->data[$key]);
			return false;
		}
		$this->data[$key]["age"] += $next;
		$block = BlockFactory::get($this->data[$key]["block"], $next);
		$ev = new BlockGrowEvent($pos->getLevel()->getBlock($pos), $block);
		$ev->call();
		if(!$ev->isCancelled()){
			$pos->getLevel()->setBlock($pos, $block);
		}
		$this->data[$key]["updatedAt"] = time();
		return true;
	}

	public function placeCrop(Block $block) : void{
		if(isset(CropIds::BLOCK_ID2CROP[$block->getId()])){
			$key = CropUtils::pos2str($block);
			if(isset($this->data[$key])){
				return;
			}
			$res = CropIds::BLOCK_ID2CROP[$block->getId()];
			$this->data[$key] = [
				"age" => 0,
				"placedAt" => time(),
				"updatedAt" => time(),
				"type" => $res
			];
			$resToCrop = CropIds::CROP_ID2BLOCK[$res];
			if(is_array($resToCrop)){
				$this->data[$key]["block"] = $resToCrop[CropIds::BLOCK];
				$this->data[$key]["stem"] = $resToCrop[CropIds::STEM];
				$face = Vector3::SIDE_NORTH;
				foreach([Vector3::SIDE_NORTH, Vector3::SIDE_SOUTH, Vector3::SIDE_SOUTH, Vector3::SIDE_EAST] as $i){
					if($block->getSide($i)->getId() === BlockIds::AIR){
						$face = $i;
						break;
					}
				}
				$this->data[$key]["side"] = $face;
			}else{
				$this->data[$key]["block"] = $resToCrop;
			}

			CropPlus::getInstance()->getLogger()->debug("Crop placed at {$block->asPosition()}($block)");
		}
	}

	public function destroyCrop(Block $block) : void{
		if(isset($this->data[CropUtils::pos2str($block)])){
			unset($this->data[CropUtils::pos2str($block)]);
			CropPlus::getInstance()->getLogger()->debug("Crop destroyed at {$block->asPosition()}($block)");
		}
	}
}

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

use pocketmine\block\BlockIds;

final class CropIds{

	public const WHEAT = 0;
	public const CARROT = 1;
	public const POTATO = 2;

	public const COCOA = 3;

	public const WATERMELON = 4;
	public const PUMPKIN = 5;

	public const STEM = 0;
	public const BLOCK = 1;

	public const CROP_ID2BLOCK = [
		self::WHEAT => BlockIds::WHEAT_BLOCK,
		self::CARROT => BlockIds::CARROTS,
		self::POTATO => BlockIds::POTATOES,
		self::WATERMELON => [
			self::STEM => BlockIds::MELON_STEM,
			self::BLOCK => BlockIds::MELON_BLOCK
		],
		self::PUMPKIN => [
			self::STEM => BlockIds::PUMPKIN_STEM,
			self::BLOCK => BlockIds::PUMPKIN
		],
		self::COCOA => BlockIds::COCOA
	];

	public const BLOCK_ID2CROP = [
		BlockIds::WHEAT_BLOCK => self::WHEAT,
		BlockIds::CARROTS => self::CARROT,
		BlockIds::POTATOES => self::POTATO,
		BlockIds::MELON_STEM => self::WATERMELON,
		BlockIds::PUMPKIN_STEM => self::PUMPKIN,
		BlockIds::COCOA => self::COCOA
	];
}
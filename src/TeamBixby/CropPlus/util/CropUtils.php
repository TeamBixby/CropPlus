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

namespace TeamBixby\CropPlus\util;

use pocketmine\level\Position;
use pocketmine\Server;

use function explode;
use function implode;

class CropUtils{

	public static function pos2str(Position $pos) : string{
		return implode(":", [
			$pos->getFloorX(),
			$pos->getFloorY(),
			$pos->getFloorZ(),
			$pos->getLevel()->getFolderName()
		]);
	}

	public static function str2pos(string $str) : Position{
		[$x, $y, $z, $world] = explode(":", $str);
		return new Position((float) $x, (float) $y, (float) $z, Server::getInstance()->getLevelByName($world));
	}
}
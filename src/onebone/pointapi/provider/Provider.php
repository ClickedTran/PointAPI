<?php

/*

 * PointS, the massive point plugin with many features for PocketMine-MP

 * Copyright (C) 2013-2017  onebone <jyc00410@gmail.com>

 *

 * This program is free software: you can redistribute it and/or modify

 * it under the terms of the GNU General Public License as published by

 * the Free Software Foundation, either version 3 of the License, or

 * (at your option) any later version.

 *

 * This program is distributed in the hope that it will be useful,

 * but WITHOUT ANY WARRANTY; without even the implied warranty of

 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the

 * GNU General Public License for more details.

 *

 * You should have received a copy of the GNU General Public License

 * along with this program.  If not, see <http://www.gnu.org/licenses/>.

 */

namespace onebone\PointAPI\provider;

use onebone\PointAPI\PointAPI;

interface Provider{

	public function __construct(PointAPI $plugin);

	public function open();

	/**

	 * @param \pocketmine\Player|string $player

	 * @return bool

	 */

	public function accountExists($player);

	/**

	 * @param \pocketmine\Player|string $player

	 * @param float $defaultPoint

	 * @return bool

	 */

	public function createAccount($player, $defaultPoint = 1000);

	/**

	 * @param \pocketmine\Player|string $player

	 * @return bool

	 */

	public function removeAccount($player);

	/**

	 * @param string $player

	 * @return float|bool

	 */

	public function getPoint($player);

	/**

	 * @param \pocketmine\Player|string $player

	 * @param float $amount

	 * @return bool

	 */

	public function setPoint($player, $amount);

	/**

	 * @param \pocketmine\Player|string $player

	 * @param float $amount

	 * @return bool

	 */

	public function addPoint($player, $amount);

	/**

	 * @param \pocketmine\Player|string $player

	 * @param float $amount

	 * @return bool

	 */

	public function reducePoint($player, $amount);

	/**

	 * @return array

	 */

	public function getAll();

	/**

	 * @return string

	 */

	public function getName();

	public function save();

	public function close();

}

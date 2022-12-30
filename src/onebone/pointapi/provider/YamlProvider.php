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

namespace onebone\pointapi\provider;


use onebone\pointapi\PointAPI;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class YamlProvider implements Provider{
    /**
     * @var Config
     */
    private $config;

    /** @var PointAPI */
    private $plugin;

    private $point = [];

    public function __construct(PointAPI $plugin){
        $this->plugin = $plugin;
    }

    public function open(){
        $this->config = new Config($this->plugin->getDataFolder() . "Point.yml", Config::YAML, ["version" => 2, "point" => []]);
        $this->point = $this->config->getAll();
    }

    public function accountExists($player){
        if($player instanceof Player){
            $player = $player->getName();
        }
        $player = strtolower($player);

        return isset($this->point["point"][$player]);
    }

    public function createAccount($player, $defaultPoint = 1000){
        if($player instanceof Player){
            $player = $player->getName();
        }
        $player = strtolower($player);

        if(!isset($this->point["point"][$player])){
            $this->point["point"][$player] = $defaultPoint;
            return true;
        }
        return false;
    }

    public function removeAccount($player){
        if($player instanceof Player){
            $player = $player->getName();
        }
        $player = strtolower($player);

        if(isset($this->point["point"][$player])){
            unset($this->point["point"][$player]);
            return true;
        }
        return false;
    }

    public function getPoint($player){
        if($player instanceof Player){
            $player = $player->getName();
        }
        $player = strtolower($player);

        if(isset($this->point["point"][$player])){
            return $this->point["point"][$player];
        }
        return false;
    }

    public function setPoint($player, $amount){
        if($player instanceof Player){
            $player = $player->getName();
        }
        $player = strtolower($player);

        if(isset($this->point["point"][$player])){
            $this->point["point"][$player] = $amount;
            $this->point["point"][$player] = round($this->point["point"][$player], 2);
            return true;
        }
        return false;
    }

    public function addPoint($player, $amount){
        if($player instanceof Player){
            $player = $player->getName();
        }
        $player = strtolower($player);

        if(isset($this->point["point"][$player])){
            $this->point["point"][$player] += $amount;
            $this->point["point"][$player] = round($this->point["point"][$player], 2);
            return true;
        }
        return false;
    }

    public function reducePoint($player, $amount){
        if($player instanceof Player){
            $player = $player->getName();
        }
        $player = strtolower($player);

        if(isset($this->point["point"][$player])){
            $this->point["point"][$player] -= $amount;
            $this->point["point"][$player] = round($this->point["point"][$player], 2);
            return true;
        }
        return false;
    }

    public function getAll(){
        return isset($this->point["point"]) ? $this->point["point"] : [];
    }

    public function save(){
        $this->config->setAll($this->point);
        $this->config->save();
    }

    public function close(){
        $this->save();
    }

    public function getName(){
        return "Yaml";
    }
}

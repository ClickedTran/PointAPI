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
use onebone\pointapi\task\MySQLPingTask;

use pocketmine\player\Player;

class MySQLProvider implements Provider{
    /**
     * @var \mysqli
     */
    private $db;

    /** @var PointAPI */
    private $plugin;

    public function __construct(PointAPI $plugin){
        $this->plugin = $plugin;
    }

    public function open(){
        $config = $this->plugin->getConfig()->get("provider-settings", []);

        $this->db = new \mysqli(
            $config["host"] ?? "127.0.0.1",
            $config["user"] ?? "onebone",
            $config["password"] ?? "hello_world",
            $config["db"] ?? "pointapi",
            $config["port"] ?? 3306);
        if($this->db->connect_error){
            $this->plugin->getLogger()->critical("Could not connect to MySQL server: ".$this->db->connect_error);
            return;
        }
        if(!$this->db->query("CREATE TABLE IF NOT EXISTS user_point(
            username VARCHAR(20) PRIMARY KEY,
            point FLOAT
        );")){
            $this->plugin->getLogger()->critical("Error creating table: " . $this->db->error);
            return;
        }

        $this->plugin->getScheduler()->scheduleRepeatingTask(new MySQLPingTask($this->plugin, $this->db), 600);
    }

    /**
     * @param \pocketmine\Player|string $player
     * @return bool
     */
    public function accountExists($player){
        if($player instanceof Player){
            $player = $player->getName();
        }
        $player = strtolower($player);

        $result = $this->db->query("SELECT * FROM user_point WHERE username='".$this->db->real_escape_string($player)."'");
        return $result->num_rows > 0 ? true:false;
    }

    /**
     * @param \pocketmine\Player|string $player
     * @param float $defaultPoint
     * @return bool
     */
    public function createAccount($player, $defaultPoint = 1000.0){
        if($player instanceof Player){
            $player = $player->getName();
        }
        $player = strtolower($player);

        if(!$this->accountExists($player)){
            $this->db->query("INSERT INTO user_point (username, point) VALUES ('".$this->db->real_escape_string($player)."', $defaultPoint);");
            return true;
        }
        return false;
    }

    /**
     * @param \pocketmine\Player|string $player
     * @return bool
     */
    public function removeAccount($player){
        if($player instanceof Player){
            $player = $player->getName();
        }
        $player = strtolower($player);

        if($this->db->query("DELETE FROM user_point WHERE username='".$this->db->real_escape_string($player)."'") === true) return true;
        return false;
    }

    /**
     * @param string $player
     * @return float|bool
     */
    public function gePoint($player){
        if($player instanceof Player){
            $player = $player->getName();
        }
        $player = strtolower($player);

        $res = $this->db->query("SELECT point FROM user_point WHERE username='".$this->db->real_escape_string($player)."'");
        $ret = $res->fetch_array()[0] ?? false;
        $res->free();
        return $ret;
    }

    /**
     * @param \pocketmine\Player|string $player
     * @param float $amount
     * @return bool
     */
    public function setPoint($player, $amount){
        if($player instanceof Player){
            $player = $player->getName();
        }
        $player = strtolower($player);

        $amount = (float) $amount;

        return $this->db->query("UPDATE user_point SET point = $amount WHERE username='".$this->db->real_escape_string($player)."'");
    }

    /**
     * @param \pocketmine\Player|string $player
     * @param float $amount
     * @return bool
     */
    public function addPoint($player, $amount){
        if($player instanceof Player){
            $player = $player->getName();
        }
        $player = strtolower($player);

        $amount = (float) $amount;

        return $this->db->query("UPDATE user_point SET point = point + $amount WHERE username='".$this->db->real_escape_string($player)."'");
    }

    /**
     * @param \pocketmine\Player|string $player
     * @param float $amount
     * @return bool
     */
    public function reducePoint($player, $amount){
        if($player instanceof Player){
            $player = $player->getName();
        }
        $player = strtolower($player);

        $amount = (float) $amount;

        return $this->db->query("UPDATE user_point SET point = point - $amount WHERE username='".$this->db->real_escape_string($player)."'");
    }

    /**
     * @return array
     */
    public function getAll(){
        $res = $this->db->query("SELECT * FROM user_point");

        $ret = [];
        foreach($res->fetch_all() as $val){
            $ret[$val[0]] = $val[1];
        }

        $res->free();

        return $ret;
    }

    /**
     * @return string
     */
    public function getName(){
        return "MySQL";
    }

    public function save(){}

    public function close(){
        if($this->db instanceof \mysqli){
            $this->db->close();
        }
    }
}

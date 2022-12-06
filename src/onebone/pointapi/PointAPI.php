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

namespace onebone\pointapi;

use pocketmine\player\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;

use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\utils\Config;

use pocketmine\utils\Utils;

use pocketmine\utils\TextFormat;

use onebone\pointapi\provider\Provider;

use onebone\pointapi\provider\YamlProvider;

use onebone\pointapi\provider\MySQLProvider;

use onebone\pointapi\event\point\SetPointEvent;

use onebone\pointapi\event\point\ReducePointEvent;

use onebone\pointapi\event\point\AddPointEvent;

use onebone\pointapi\event\point\PointChangedEvent;

use onebone\pointapi\event\account\CreateAccountEvent;

use onebone\pointapi\task\SaveTask;

class PointAPI extends PluginBase implements Listener{

    const API_VERSION = 4;

    const PACKAGE_VERSION = "3.0.1";

    const RET_NO_ACCOUNT = -3;

    const RET_CANCELLED = -2;

    const RET_NOT_FOUND = -1;

    const RET_INVALID = 0;

    const RET_SUCCESS = 1;

    private static $instance = null;

    /** @var Provider */

    private $provider;

    private $langList = [

        "vie" => "Tiếng Việt",

        "def" => "Default",

        "user-define" => "User Defined",

        "ch" => "简体中文",

        "cs" => "Čeština",

        "en" => "English",

        "fr" => "Français",

        "id" => "Bahasa Indonesia",

        "it" => "Italiano",

        "ja" => "日本語",

        "ko" => "한국어",

        "nl" => "Nederlands",

        "por" => "Portugues",

        "ru" => "Русский",

        "uk" => "Українська",

        "zh" => "繁體中文",

    ];

    private $lang = [], $playerLang = [];

    /**

     * @param string            $command

     * @param string|bool        $lang

     *

     * @return array

     */

    public function getCommandMessage(string $command, $lang = false) : array{

        if($lang === false){

        }

        $command = strtolower($command);

        if(isset($this->lang["vie"]["commands"][$command])){

            return $this->lang["vie"]["commands"][$command];

        }else{

            return $this->lang["vie"]["commands"][$command];

        }

    }

    /**

     * @param string        $key

     * @param array         $params

     * @param string        $player

     *

     * @return string

     */

    public function getMessage(string $key, array $params = [], string $player = "console") : string{

        $player = strtolower($player);

        if(isset($this->lang[$this->playerLang[$player]][$key])){

            return $this->replaceParameters($this->lang[$this->playerLang[$player]][$key], $params);

        }elseif(isset($this->lang["vie"][$key])){

            return $this->replaceParameters($this->lang["vie"][$key], $params);

        }

        return "Language matching key \"$key\" does not exist.";

    }

    public function setPlayerLanguage(string $player, string $language) : bool{

        $player = strtolower($player);

        $language = strtolower($language);

        if(isset($this->lang[$language])){

            $this->playerLang[$player] = $language;

            return true;

        }

        return false;

    }

    public function getMonetaryUnit() : string{

        return $this->getConfig()->get("monetary-unit");

    }

    /**

     * @return array

     */

    public function getAllPoint() : array{

        return $this->provider->getAll();

    }

    /**

     * @param string|Player        $player

     * @param float                $defaultPoint

     * @param bool                $force

     *

     * @return bool

     */

    public function createAccount($player, $defaultPoint = false, bool $force = false) : bool{

        if($player instanceof Player){

            $player = $player->getName();

        }

        $player = strtolower($player);

        if(!$this->provider->accountExists($player)){

            $defaultPoint = ($defaultPoint === false) ? $this->getConfig()->get("default-point") : $defaultPoint;

           $ev = $ev = new CreateAccountEvent($this, $player, $defaultPoint, "none");

           $ev->call();

            if(!$ev->isCancelled() or $force === true){

                $this->provider->createAccount($player, $ev->getDefaultPoint());

            }

        }

        return false;

    }

    /**

     * @param string|Player            $player

     *

     * @return bool

     */

    public function accountExists($player) : bool{

        return $this->provider->accountExists($player);

    }

    /**

     * @param Player|string        $player

     *

     * @return float|bool

     */

    public function myPoint($player){

        return $this->provider->getPoint($player);

    }

    /**

     * @param string|Player     $player

     * @param float             $amount

     * @param bool                $force

     * @param string            $issuer

     *

     * @return int

     */

    public function setPoint($player, $amount, bool $force = false, string $issuer = "none") : int{

        if($amount < 0){

            return self::RET_INVALID;

        }

        if($player instanceof Player){

            $player = $player->getName();

        }

        $player = strtolower($player);

        if($this->provider->accountExists($player)){

            $amount = round($amount, 2);

            if($amount > $this->getConfig()->get("max-point")){

                return self::RET_INVALID;

            }

  

            $ev = new SetPointEvent($this, $player, $amount, $issuer);

            $ev->call();

            if(!$ev->isCancelled() or $force === true){

                $this->provider->setPoint($player, $amount);

                $ev2 = new PointChangedEvent($this, $player, $amount, $issuer);

                $ev2->call();

                return self::RET_SUCCESS;

            }

            return self::RET_CANCELLED;

        }

        return self::RET_NO_ACCOUNT;

    }

    /**

     * @param string|Player     $player

     * @param float             $amount

     * @param bool                $force

     * @param string            $issuer

     *

     * @return int

     */

    public function addPoint($player, $amount, bool $force = false, $issuer = "none") : int{

        if($amount < 0){

            return self::RET_INVALID;

        }

        if($player instanceof Player){

            $player = $player->getName();

        }

        $player = strtolower($player);

        if(($point = $this->provider->getPoint($player)) !== false){

            $amount = round($amount, 2);

            if($point + $amount > $this->getConfig()->get("max-point")){

                return self::RET_INVALID;

            }

           

            $ev = new AddPointEvent($this, $player, $amount, $issuer);

            $ev->call();

            if(!$ev->isCancelled() or $force === true){

                $this->provider->addPoint($player, $amount);

                $ev2 = new PointChangedEvent($this, $player, $amount + $point, $issuer);

                $ev2->call();

                return self::RET_SUCCESS;

            }

            return self::RET_CANCELLED;

        }

        return self::RET_NO_ACCOUNT;

    }

    /**

     * @param string|Player     $player

     * @param float             $amount

     * @param bool                $force

     * @param string            $issuer

     *

     * @return int

     */

    public function reducePoint($player, $amount, bool $force = false, $issuer = "none") : int{

        if($amount < 0){

            return self::RET_INVALID;

        }

        if($player instanceof Player){

            $player = $player->getName();

        }

        $player = strtolower($player);

        if(($point = $this->provider->getPoint($player)) !== false){

            $amount = round($amount, 2);

            if($point - $amount < 0){

                return self::RET_INVALID;

            }

           

            $ev = new ReducePointEvent($this, $player, $amount, $issuer);

            $ev->call();

             if(!$ev->isCancelled() or $force === true){

                $this->provider->reducePoint($player, $amount);

                $ev2 = new PointChangedEvent($this, $player, $point - $amount, $issuer);

               $ev2->call();

              return self::RET_SUCCESS;

            }

            return self::RET_CANCELLED;

        }

        return self::RET_NO_ACCOUNT;

    }

    /**

     * @return PointAPI

     */

    public static function getInstance(){

        return self::$instance;

    }

    public function onLoad() : void{

        self::$instance = $this;

    }

    public function onEnable() : void{

        /*

         * 디폴트 설정 파일을 먼저 생성하게 되면 데이터 폴더 파일이 자동 생성되므로

         * 'Failed to open stream: No such file or directory' 경고 메시지를 없앨 수 있습니다

         * - @64FF00

         *

         * [추가 옵션]

         * if(!file_exists($this->dataFolder))

         *     mkdir($this->dataFolder, 0755, true);

         */

        $this->saveDefaultConfig();

        if(!is_file($this->getDataFolder()."PlayerLang.dat")){

            file_put_contents($this->getDataFolder()."PlayerLang.dat", serialize([]));

        }

        $this->playerLang = unserialize(file_get_contents($this->getDataFolder()."PlayerLang.dat"));

        if(!isset($this->playerLang["console"])){

            $this->playerLang["console"] = $this->getConfig()->get("default-lang");

        }

        if(!isset($this->playerLang["rcon"])){

            $this->playerLang["rcon"] = $this->getConfig()->get("default-lang");

        }

        $this->initialize();

        if($this->getConfig()->get("auto-save-interval") > 0){

            $this->getScheduler()->scheduleDelayedRepeatingTask(new SaveTask($this), $this->getConfig()->get("auto-save-interval") * 1200, $this->getConfig()->get("auto-save-interval") * 1200);

        }

        $this->getServer()->getPluginManager()->registerEvents($this, $this);

    }

    public function onJoin(PlayerJoinEvent $event){

        $player = $event->getPlayer();

        if(!isset($this->playerLang[strtolower($player->getName())])){

            $this->playerLang[strtolower($player->getName())] = $this->getConfig()->get("default-lang");

        }

        if(!$this->provider->accountExists($player)){

            $this->getLogger()->debug("Account of '".$player->getName()."' is not found. Creating account...");

            $this->createAccount($player, false, true);

        }

    }

    public function onDisable() : void{

        $this->saveAll();

        if($this->provider instanceof Provider){

            $this->provider->close();

        }

    }

    public function saveAll(){

        if($this->provider instanceof Provider){

            $this->provider->save();

        }

        file_put_contents($this->getDataFolder()."PlayerLang.dat", serialize($this->playerLang));

    }

    private function replaceParameters($message, $params = []){

        $search = ["%MONETARY_UNIT%"];

        $replace = [$this->getMonetaryUnit()];

        for($i = 0; $i < count($params); $i++){

            $search[] = "%".($i + 1);

            $replace[] = $params[$i];

        }

        $colors = [

            "0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f", "k", "l", "m", "n", "o", "r"

        ];

        foreach($colors as $code){

            $search[] = "&".$code;

            $replace[] = TextFormat::ESCAPE.$code;

        }

        return str_replace($search, $replace, $message);

    }

    private function initialize(){

      /** if($this->getConfig()->get("check-update")){

            $this->checkUpdate();

        }**/

        switch(strtolower($this->getConfig()->get("provider"))){

            case "yaml":

            $this->provider = new YamlProvider($this);

            break;

            case "mysql":

            $this->provider = new MySQLProvider($this);

            break;

            default:

            $this->getLogger()->critical("Invalid database was given.");

            return false;

        }

        $this->provider->open();

        $this->initializeLanguage();

        $this->getLogger()->notice("Database provider was set to: ".$this->provider->getName());

        $this->registerCommands();

    }

    public function openProvider(){

        if($this->provider !== null)

            $this->provider->open();

    }

    /**private function checkUpdate(){

        try{

            $info = json_decode(Internet::getURL($this->getConfig()->get("update-host")."?version=".$this->getDescription()->getVersion()."&package_version=".self::PACKAGE_VERSION), true);

            if(!isset($info["status"]) or $info["status"] !== true){

                $this->getLogger()->notice("Something went wrong on update server.");

                return false;

            }

            if($info["update-available"] === true){

                $this->getLogger()->notice("Server says new version (".$info["new-version"].") of PointS is out. Check it out at ".$info["download-address"]);

            }

            $this->getLogger()->notice($info["notice"]);

            return true;

        }catch(\Throwable $e){

            $this->getLogger()->logException($e);

            return false;

        }

    }**/

    private function registerCommands(){

        $map = $this->getServer()->getCommandMap();

        $commands = [

            "mypoint" => "\\onebone\\pointapi\\command\\MyPointCommand",

            "toppoint" => "\\onebone\\pointapi\\command\\TopPointCommand",

            "setpoint" => "\\onebone\\pointapi\\command\\SetPointCommand",

            "seepoint" => "\\onebone\\pointapi\\command\\SeePointCommand",

            "givepoint" => "\\onebone\\pointapi\\command\\GivePointCommand",

            "takepoint" => "\\onebone\\pointapi\\command\\TakePointCommand",

            "paypoint" => "\\onebone\\pointapi\\command\\PayPointCommand",

            "setlangpoint" => "\\onebone\\pointapi\\command\\SetLangCommand",

            "mystatuspoint" => "\\onebone\\pointapi\\command\\MyStatusPointCommand"

        ];

        foreach($commands as $cmd => $class){

            $map->register("pointapi", new $class($this));

        }

    }

    private function initializeLanguage(){

        foreach($this->getResources() as $resource){

            if($resource->isFile() and substr(($filename = $resource->getFilename()), 0, 5) === "lang_"){

                $this->lang[substr($filename, 5, -5)] = json_decode(file_get_contents($resource->getPathname()), true);

            }

        }

        $this->lang["user-define"] = (new Config($this->getDataFolder()."messages.yml", Config::YAML, $this->lang["vie"]))->getAll();

    }

}


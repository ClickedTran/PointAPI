<?php

namespace onebone\pointapi\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\player\Player;
use pocketmine\Server;

use onebone\pointapi\PointAPI;

class SeePointCommand extends Command{
    private $plugin;

    public function __construct(PointAPI $plugin){
        $desc = $plugin->getCommandMessage("seepoint");
        parent::__construct("seepoint", $desc["description"], $desc["usage"]);

        $this->setPermission("pointapi.command.seepoint");

        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $label, array $params): bool{
        if(!$this->plugin->isEnabled()) return false;
        if(!$this->testPermission($sender)){
            return false;
        }

        $player = array_shift($params);
        if(trim($player) === ""){
            $sender->sendMessage(TextFormat::RED . "Usage: " . $this->getUsage());
            return true;
        }

        if(($p = $this->plugin->getServer()->getPlayerByPrefix($player)) instanceof Player){
            $player = $p->getName();
        }

        $point = $this->plugin->myPoint($player);
        if($point !== false){
            $sender->sendMessage($this->plugin->getMessage("seepoint-seepoint", [$player, $point], $sender->getName()));
        }else{
            $sender->sendMessage($this->plugin->getMessage("player-never-connected", [$player], $sender->getName()));
        }
        return true;
    }
}

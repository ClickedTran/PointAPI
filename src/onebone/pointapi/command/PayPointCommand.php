<?php

namespace onebone\pointapi\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\player\Player;
use pocketmine\Server;
use onebone\pointapi\PointAPI;
use onebone\pointapi\event\pointapj\PayPointEvent;

class PayPointCommand extends Command{
    private $plugin;

    public function __construct(PointAPI $plugin){
        $desc = $plugin->getCommandMessage("paypoint");
        parent::__construct("paypoint", $desc["description"], $desc["usage"]);

        $this->setPermission("pointapi.command.paypoint");

        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $label, array $params): bool{
        if(!$this->plugin->isEnabled()) return false;
        if(!$this->testPermission($sender)){
            return false;
        }

        if(!$sender instanceof Player){
            $sender->sendMessage(TextFormat::RED . "Please run this command in-game.");
            return true;
        }

        $player = array_shift($params);
        $amount = array_shift($params);

        if(!is_numeric($amount)){
            $sender->sendMessage(TextFormat::RED . "Usage: " . $this->getUsage());
            return true;
        }

        if(($p = $this->plugin->getServer()->getPlayerByPrefix($player)) instanceof Player){
            $player = $p->getName();
        }

        if(!$p instanceof Player and $this->plugin->getConfig()->get("allow-pay-offline", true) === false){
            $sender->sendMessage($this->plugin->getMessage("player-not-connected", [$player], $sender->getName()));
            return true;
        }

        if(!$this->plugin->accountExists($player)){
            $sender->sendMessage($this->plugin->getMessage("player-never-connected", [$player], $sender->getName()));
            return true;
        }
  
        $ev = new PayPointEvent($this->plugin, $sender->getName(), $player, $amount);
        $ev->call();
        $result = PointAPI::RET_CANCELLED;
        if(!$ev->isCancelled()){
            $result = $this->plugin->reducePoint($sender, $amount);
        }

        if($result === PointAPI::RET_SUCCESS){
            $this->plugin->addPoint($player, $amount, true);

            $sender->sendMessage($this->plugin->getMessage("pay-success", [$amount, $player], $sender->getName()));
            if($p instanceof Player){
                $p->sendMessage($this->plugin->getMessage("point-paid", [$sender->getName(), $amount], $sender->getName()));
            }
        }else{
            $sender->sendMessage($this->plugin->getMessage("pay-failed", [$player, $amount], $sender->getName()));
        }
        return true;
    }
}

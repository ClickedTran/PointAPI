<?php

namespace onebone\pointapi\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\player\Player;

use onebone\pointapi\PointAPI;

class MyStatusPointCommand extends Command{
    private $plugin;

    public function __construct(PointAPI $plugin){
        $desc = $plugin->getCommandMessage("mystatuspoint");
        parent::__construct("mystatuspoint", $desc["description"], $desc["usage"]);

        $this->setPermission("pointapi.command.mystatuspoint");

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

        $point = $this->plugin->getAllPoint();

        $allPoint = 0;
        foreach($point as $m){
            $allPoint += $m;
        }
        $topPoint = 0;
        if($allPoint > 0){
            $topPoint = round((($point[strtolower($sender->getName())] / $allPoint) * 100), 2);
        }

        $sender->sendMessage($this->plugin->getMessage("mystatuspp-show", [$topPoint], $sender->getName()));
        return true;
    }
}

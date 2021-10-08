<?php 

declare(strict_types=1);

namespace NoobMCBG\ItemTell;

use pocketmine\plugin\PluginBase as PB;
use pocketmine\event\Listener as L;
use pocketmine\utils\TextFormat as TF; 

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerInteractEvent as CL;

use pocketmine\command\{CommandSender, Command};
use pocketmine\utils\Config;

use pocketmine\item\Item;
use pocketmine\inventory\Inventory;

use libs\jojoe77777\FormAPI\{CustomForm, SimpleForm};

class Main extends PB implements L {
		
	public $cfg;
	
	public function onLoad(){
		$this->getLogger()->info("Loading Plugin");
	}
	
	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
	    $this->user = new Config($this->getDataFolder() . "item.yml", Config::YAML);
		$this->saveResource("config.yml");
		$cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$cfg->save();
		if($cfg->get("joinItem") == "false"){
                $this->getLogger()->info(" When you join, you will receive disabled items");
		} elseif($cfg->get("joinItem") == "true"){
                $this->getLogger()->info(" When you join, you will receive enabled items");
		}
		
		$this->getLogger()->info("Enable Plugin");
	}
	
	public function onDisable() {
		$this->getLogger()->info("Disable Plugin");
	}
	
	public function getNameItem($player){
		if($player instanceof Player){
			$name = $player->getName();
		}
		$this->user->load($this->getDataFolder() . "item.yml", Config::YAML);
		$cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$cfg->save();
	    $itemname = $cfg->get("item-name");
		return $itemname;
	}
	
	public function getLore(){
		$cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$cfg->save();
	    $lore = $cfg->get("item-lore");
	    return $lore;
    }
	
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{
        $cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$cfg->save();
		switch($cmd->getName()){
		       case "giveitemtell":
                   if(!$sender->hasPermission("itemtell.command.give")){
                       $sender->sendMessage($cfg->get("no-permission"));
                   }
                   if(!isset($args[0])){
                       $sender->sendMessage("§cUsage: /giveitemtell <player>");
                       return true;
                   }else{
                      $player = $this->getServer()->getPlayer($args[0]);
                      if(!$player == null){
                          if($player->isOnline()) {
                              $p = $player;
                              $inv = $player->getInventory();
				              $item = Item::get($cfg->get("item-id"), $cfg->get("item-meta"), $cfg->get("item-amount"));
                              $itemname = $this->getNameItem($player);
                              $item->setCustomName($itemname);
                              $item->setLore(array($this->getLore()));
                              $inv->addItem($item);
                          }
                      }
                   }
             break;
        }
		return true;
	}
	
	public function onJoin(PlayerJoinEvent $event){
		$cfg = new Config($this->getDataFolder()."config.yml", Config::YAML);
		$cfg->getAll();
                $player = $event->getPlayer();
                $inventory = $player->getInventory();
                $pn = $player->getName();
                $name = $pn;
                $level = $player->getLevel();
		 
		if ($cfg->get("joinItem") == "true"){
			
		  $i = Item::get(328, 0, 1);
		  $i->setCustomName($this->getNameItem($player));
		  $i->setLore(array($this->getLore()));
          $inventory->addItem($i);
		}
	}
	
	 public function setItem(CL $event){
		$cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$cfg->save();
		$player = $event->getPlayer();
        $name = $player->getName();
		$item = $player->getInventory()->getItemInHand();
		if ($item->getCustomName() == $cfg->get("item-name")){
			$this->ItemForm($player);
		}
	 }
	 
	 public function ItemForm($player) {
		$cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$cfg->save();
		$form = new CustomForm(function (Player $player, $data){
			if(!$data == null){
			$this->getServer()->getCommandMap()->dispatch($player, "tell ".$data[0]." ".$data[1]);
			}
		});
		$form->setTitle("Menu Tell");
		$form->addInput("§l§c[!]§b Player Tell", "Name...");
        $form->addInput("§l§c[!]§b Messages Tell", "Hey... !");
		$form->sendToPlayer($player);
	}
}
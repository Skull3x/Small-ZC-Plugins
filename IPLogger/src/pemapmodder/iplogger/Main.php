<?php

namespace pemapmodder\iplogger;

use pocketmine\Player;
use pocketmine\command\Command as Cmd;
use pocketmine\command\CommandSender as Issuer;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener{
	private $pdir;
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		@mkdir($this->getDataFolder());
		if(!is_dir($this->pdir = $this->getDataFolder()."players/")){
			mkdir($this->pdir);
			$this->getLogger()->debug("IPLogger folder generated at {$this->pdir}.");
		}
		else{
			$this->getLogger()->debug("IPLogger folder already generated at {$this->pdir}.");
		}
	}
	public function onJoin(PlayerJoinEvent $event){
		$p = $event->getPlayer();
		@touch($file = $this->getPlayerFile($p));
		$ips = explode(PHP_EOL, file_get_contents($file)); // I HATE the annoying EOL difference. Why can't everyone just use the same one?
		if(!in_array($ip = $p->getAddress(), $ips)){
			$ips[] = $ip;
			sort($ips, SORT_STRING); // don't sort_natural
		}
	}
	public function onCommand(Issuer $issuer, Cmd $cmd, $alias, array $args){
		if(!isset($args[0])){
			if(!$issuer->hasPermission("iplogger.self.read")){
				$issuer->sendMessage("You don't have permission to view your own IP log!");
				return true;
			}
			if(!($issuer instanceof Player)){
				return false; // request pass arg 0
			}
			$name = strtolower($issuer->getName());
		}
		else{
			if(!$issuer->hasPermission("iplogger.other.read")){
				$issuer->sendMessage("You don't have permission to view otheres' IP log!");
				return true;
			}
			$name = strtolower(trim($args[0]));
		}
		$path = $this->getFileByString($name);
		if(!is_file($path)){
			$issuer->sendMessage("$name has never been on this server!");
			return true;
		}
		$issuer->sendMessage("IP log of $name:");
		$msg = str_replace(PHP_EOL, ", ", file_get_contents($path));
		if(substr($msg, -2) === ", "){
			$msg = substr($msg, 0, -2);
		}
		$issuer->sendMessage($msg);
		return true;
	}
	public function getPlayerFile(Player $player){
		return $this->getFileByString(strtolower($player->getName()));
	}
	public function getFileByString($name){
		return $this->pdir.$name.".list";
	}
}

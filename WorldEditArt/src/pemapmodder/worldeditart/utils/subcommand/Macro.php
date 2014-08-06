<?php

namespace pemapmodder\worldeditart\utils\subcommand;

use pemapmodder\worldeditart\utils\macro\Macro as MacroObj;
use pocketmine\level\Position;
use pocketmine\Player;

class Macro extends Subcommand{
	public function getName(){
		return "macro";
	}
	public function getDescription(){
		return "Manage macros";
	}
	public function getUsage(){
		return "<start [a|anchor]|ng|save <name>|wait <ticks>|pause|resume>";
	}
	public function checkPermission(Player $player){
		// TODO
	}
	public function onRun(array $args, Player $player){
		if(!isset($args[0])){
			return self::WRONG_USE;
		}
		switch($sub = strtolower(array_shift($args))){
			case "start":
				if($this->getMain()->getRecordingMacro($player) instanceof MacroObj){
					return "You are already recording a macro!";
				}
				$anchor = $player->getPosition();
				while(isset($args[0])){
					$arg = array_shift($args);
					switch($arg){
						case "a":
						case "anchor":
							$anchor = $this->getMain()->getAnchor($player);
							if(!($anchor instanceof Position)){
								return self::NO_ANCHOR;
							}
					}
				}
				$macro = new MacroObj(false, $anchor, $player);
				$this->getMain()->setRecordingMacro($player, $macro);
				return "You are now recording a macro.";
			case "save":
			case "wait":
				if(!isset($args[0])){
					return "Usage: /w macro save <name>";
				}
			case "ng":
			case "pause":
			case "resume":
				$macro = $this->getMain()->getRecordingMacro($player);
				if(!($macro instanceof MacroObj)){
					return "You don't have a recording macro!";
				}
				break;
			default:
				return self::WRONG_USE;
		}
		switch($sub){
			case "ng":
				$this->getMain()->unsetRecordingMacro($player);
				return "Your recording macro has been terminated.";
			case "save":
				$name = array_shift($args);
				if(isset($this->getMain()->getMacroDataProvider()[$name])){
					return "Such macro already exists!";
				}
				try{
					$this->getMain()->getMacroDataProvider()[$name] = $macro;
				}
				catch(\Exception $e){
					return "An error occurred. Type: ".(new \ReflectionClass($e))->getShortName()."; Message: ".$e->getMessage();
				}
			case "wait":
				$ticks = (int) round(floatval(array_shift($args)) * 20);
				$macro->wait($ticks);
				return "A $ticks-tick waiting added.";
			case "pause":
				if($macro->isHibernating()){
					return "Your macro is already paused. Use \"/w macro resume\" to resume.";
				}
				$macro->setHibernating(true);
				return "Your macro has been paused.";
			case "resume":
				if(!$macro->isHibernating()){
					return "Your macro is running! Use \"/w macro pause\" to pause.";
				}
				$macro->setHibernating(true);
				return "Your macro has been resumed.";
		}
		return self::WRONG_USE;
	}
}

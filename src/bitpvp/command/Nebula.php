<?php

namespace bitpvp\command;

use bitpvp\registry\Registry;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
class Nebula extends Command {

    public function __construct() {
        parent::__construct('nebula', "Remove a ban caused by Nebula", ">> /pardon (playerName)");
        $this->setPermission("nebula.ac");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void  {
        if (!$this->testPermission($sender)) {
            $sender->sendMessage(TextFormat::colorize("&cYou dont have permissions to run [Nebula] command."));
            return;
        }

        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::DARK_PURPLE . '[Nebula]' . "\n" . "/pardon (playerName)");
            return;
        }

        $target = $sender->getServer()->getPlayerByPrefix($args[0]);

        if (!$target instanceof Player) {
            $sender->sendMessage(TextFormat::DARK_PURPLE . 'Argument (playerName) missing.');
            return;
        }

        if(!Registry::getInstance()->exists($target)) {
            $sender->sendMessage(TextFormat::DARK_PURPLE . "Player is not banned.");
            return;
        }

        Registry::getInstance()->remove($target);

        $sender->sendMessage(TextFormat::DARK_PURPLE . $target . " was successfully unbanned");
    }
}
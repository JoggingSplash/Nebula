<?php

namespace bitpvp\command;

use bitpvp\registry\Registry;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
class Pardon extends Command {

    public function __construct() {
        parent::__construct('pardon', "Remove a ban caused by Nebula", ">> /pardon (playerName)");
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

        if(!Registry::getInstance()->exists($args[0])) {
            $sender->sendMessage(TextFormat::DARK_PURPLE . "Player is not banned.");
            return;
        }

        Registry::getInstance()->remove($args[0]);

        $sender->sendMessage(TextFormat::DARK_PURPLE . $args[0] . " was successfully unbanned");
    }
}
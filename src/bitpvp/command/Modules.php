<?php

namespace bitpvp\command;

use bitpvp\Nebula;
use bitpvp\util\types\Translator;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class Modules extends Command {

    public function __construct()
    {
        parent::__construct('modules', "Check Active Modules", ">> /modules");
        $this->setPermission("nebula.ac");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void  {
        if (!$this->testPermission($sender)) {
            $sender->sendMessage(TextFormat::colorize("&cYou dont have permissions to run [Nebula] command."));
            return;
        }


        $message = [
            "",
            "&5[Nebula] Modules:",
            "---------------------------------",
            "&cActive:",
            "&5" . Translator::translateModule(0),
            "&5" . Translator::translateModule(1),
            "&5" . Translator::translateModule(3),
            "&5" . Translator::translateModule(4),
            "&5" . Translator::translateModule(5),
            "&5" . Translator::translateModule(6),
            "",
            "&cNot Working:",
            "&5" . Translator::translateModule(2),
            "---------------------------------",
        ];

        $sender->sendMessage(TextFormat::colorize(implode("\n", $message)));
    }
}
<?php

namespace bitpvp\command;

use bitpvp\Nebula;
use bitpvp\util\types\Translator;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class Info extends Command
{

    public function __construct()  {
        parent::__construct('info', "Check Player Info", ">> /info (playerName)]");
        $this->setPermission("nebula.ac");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void  {
        if (!$this->testPermission($sender)) {
            $sender->sendMessage(TextFormat::colorize("&cYou dont have permissions to run [Nebula] command."));
            return;
        }

        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::DARK_PURPLE . '[Nebula]' . "\n" . "/info (playerName)");
            return;
        }

        $target = $sender->getServer()->getPlayerByPrefix($args[0]);

        if (!$target instanceof Player) {
            $sender->sendMessage(TextFormat::DARK_PURPLE . 'Player was not found.');
            return;
        }

        $message = [
            "",
            "&5[Nebula] : ClientData display",
            "&r&5---------------------------------",
            "&c{$target->getName()} Info:",
            "&5Device: &c" . Translator::translateDevice($target->getNetworkSession()->getPlayerInfo()->getExtraData()['DeviceOS']),
            "&5Input: &c" . Translator::translateInputMode($target->getNetworkSession()->getPlayerInfo()->getExtraData()['CurrentInputMode']),
            "&5SSI: &c" . $target->getNetworkSession()->getPlayerInfo()->getExtraData()['SelfSignedId'],
            "&5DID: &c" . $target->getNetworkSession()->getPlayerInfo()->getExtraData()['DeviceId'],
            "&5ClientID: &c" . $target->getNetworkSession()->getPlayerInfo()->getExtraData()['ClientRandomId'],
            "&5DeviceModel: &c " . $target->getPlayerInfo()->getExtraData()["DeviceModel"],
            "&5UUID: &c" . $target->getNetworkSession()->getPlayerInfo()->getUuid(),
            "&r&5---------------------------------",
        ];

        $sender->sendMessage(TextFormat::colorize(implode("\n", $message)));
    }
}
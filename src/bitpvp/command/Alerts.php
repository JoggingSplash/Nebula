<?php

namespace bitpvp\command;

use bitpvp\Nebula;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use bitpvp\session\Session;
use bitpvp\session\SessionManager;
use pocketmine\player\Player;

class Alerts extends Command
{

	public function __construct()
	{
		parent::__construct('alerts', "Check Player Alerts", ">> /alerts [on/off/show]", []);
		$this->setPermission("nebula.ac");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args): void
	{
		if (!$this->testPermission($sender)) {
			return;
		}

		if (!$sender instanceof Player) {
			$sender->sendMessage("This command can only get executed in game");
			return;
		}

		if (count($args) < 1) {
			$sender->sendMessage(TextFormat::DARK_PURPLE . '>> /alerts [on/off/show');
			return;
		}

        if(empty($args[0])) {
            $sender->sendMessage(TextFormat::DARK_PURPLE . '>> /alerts [on/off/show]');
            return;
        }

		$session = SessionManager::getInstance()->getSession($sender);

		if ($args[0] === 'on') {

            if($session->enableAlerts) {
                $sender->sendMessage(TextFormat::DARK_PURPLE . "You alredy have enabled alerts!");
                return;
            }

			$session->setEnableAlerts();
			$sender->sendMessage(TextFormat::DARK_PURPLE . ">> [ Nebula ] " . "\n" .'>> Enabled alerts' . "\n");
			return;
		}

        if ($args[0] === 'off') {

            if(!$session->enableAlerts) {
                $sender->sendMessage(TextFormat::DARK_PURPLE . "You alredy have diabled alerts!");
                return;
            }

			$session->setEnableAlerts(false);
			$sender->sendMessage(TextFormat::DARK_PURPLE . ">> [ Nebula ] " . "\n" .'>> Disabled alerts' . "\n");
			return;
		}

		if ($args[0] === 'show') {

			if (count($args) < 2) {
				$sender->sendMessage(TextFormat::DARK_PURPLE . 'Argument (playerName) missing');
				return;
			}

			$target = Server::getInstance()->getPlayerByPrefix($args[1]);
			if ($target === null) {
				$sender->sendMessage(TextFormat::DARK_PURPLE . 'Player was not found.');
				return;
			}

			$session = SessionManager::getInstance()->getSession($target);

			if (!$session instanceof Session) {
				$sender->sendMessage(TextFormat::DARK_PURPLE . 'Player was not found.');
				return;
			}

			$message = [
                "",
                "&l&5[Nebula] &r&cAlerts will be reset each relog of the player",
				"&r&5---------------------------------",
				"&c{$target->getName()}'s Alerts:",
				'&r&cReach: &5x' . $session->getReachViolations(),
				'&r&cTimer: &5x' . $session->getTimerViolations(),
				'&r&cVelocity: &5x' . $session->getVelocityViolations(),
                '&r&cAutoClick: &5x'. $session->getAutoViolations(),
                '&r&cPackets: &5x' . $session->getPacketsViolations(),
				"&5---------------------------------"
			];

			$sender->sendMessage(TextFormat::colorize(implode("\n", $message)));
		}
	}
}
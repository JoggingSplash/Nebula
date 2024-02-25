<?php

namespace bitpvp;

use bitpvp\registry\Registry;
use bitpvp\session\Session;
use bitpvp\session\SessionManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerChangeSkinEvent;
use pocketmine\scheduler\ClosureTask;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;

class EventHandler implements Listener {
    use SingletonTrait;


    public function handleLogin(PlayerLoginEvent $event): void {
        $player = $event->getPlayer();
        SessionManager::getInstance()->createSession($player);
        $session = SessionManager::getInstance()->getSession($player);

        if(!$session instanceof Session) {
            $player->kick("There was a problem creating your session, please try again.");
            return;
        }

        if(Registry::getInstance()->exists($player->getName())) {
            $player->kick("You are banned from this Network.");
            return;
        }

        Nebula::getInstance()->getServer()->getLogger()->debug(TextFormat::colorize("&5[Nebula] Created Session to: ". $player->getName()));

        if($session->isWaitTime()) {
            Nebula::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($session): void {
                $session->setWaitTime(false);
            }), 20 * 7);
        }

    }

    public function handleQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();

        if(!$player instanceof Player) {
            return;
        }

        SessionManager::getInstance()->removeSession($player);
        Nebula::getInstance()->getServer()->getLogger()->debug(TextFormat::colorize("&5[Nebula] Removed Session from: ". $player->getName()));
    }

    public function handleSkinChange(PlayerChangeSkinEvent $event): void {
        $player = $event->getPlayer();

        if (!$player instanceof Player) {
            return;
        }

        $event->cancel();
        $player->sendMessage(TextFormat::DARK_PURPLE . 'You cant change your skin in-game. If you want to change it, please relog.');
    }

}
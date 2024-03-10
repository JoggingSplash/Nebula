<?php

namespace bitpvp\module\preset;

use bitpvp\module\IModule;
use bitpvp\session\Session;
use bitpvp\session\SessionManager;
use bitpvp\util\Util;
use pocketmine\event\Listener;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\player\Player;
use pocketmine\event\server\DataPacketReceiveEvent;

class Packets extends IModule implements Listener {

    public function __construct(){
        parent::__construct(self::PACKETS);
    }

    public function packets(DataPacketReceiveEvent $event): void {
        $player = $event->getOrigin()->getPlayer();
        $packet = $event->getPacket();

        if (!$player instanceof Player){
            return;
        }

        if (!$player->spawned or !$player->isSurvival() or !$player->isOnline()) {
            return;
        }

        $session = SessionManager::getInstance()->getSession($player);
        if (!$session instanceof Session){
            return;
        }

        if($packet instanceof PlayerAuthInputPacket){
            $session->packetsDelay++;
        }elseif($packet instanceof MovePlayerPacket){
            if($session->packetsDelay < 2 && !$player->hasNoClientPredictions() && $player->isAlive()){
                $session->addPacketsViolations();
                Util::getInstance()->log($this->getFlagId(), $player, $session->getPacketsViolations(), $session->packetsDelay);
            }
            $session->packetsDelay = 0;
        }
    }
}
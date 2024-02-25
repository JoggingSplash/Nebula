<?php

namespace bitpvp\module\preset;

use bitpvp\module\IModule;
use bitpvp\network\NetworkCpsManager;
use bitpvp\session\Session;
use bitpvp\session\SessionManager;
use bitpvp\util\ModuleUtil;
use bitpvp\util\types\Translator;
use bitpvp\util\Util;
use Exception;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMissSwingEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\player\Player;

class Autoclick extends IModule implements Listener {

    public function __construct() {
        parent::__construct(self::AUTOCLICK);
    }

    /**
     * @priority HIGHEST
     * @throws Exception
     */

    public function cps(DataPacketReceiveEvent $ev): void {
        $player = $ev->getOrigin()->getPlayer();
        $packet = $ev->getPacket();

        if (!$player instanceof Player) {
            return;
        }

        if (!$player->isOnline()) {
            return;
        }

        $session = SessionManager::getInstance()->getSession($player);

        if ($packet instanceof LevelSoundEventPacket) {
            if ($packet->sound === LevelSoundEvent::ATTACK_NODAMAGE) {
                $networkCpsManager = NetworkCpsManager::getInstance();
                $networkCpsManager->addCps($player);
                if($networkCpsManager->getCps($player) > 20) {
                    $session->addAutoViolation();
                    Util::getInstance()->log($this->getFlagId(), $player, $session->getAutoViolations(), $networkCpsManager->getCps($player));
                    $this->checkAlerts($player);
                }
            }
        }

        if ($packet instanceof InventoryTransactionPacket) {
            if ($packet->trData instanceof UseItemOnEntityTransactionData) {
                $networkCpsManager = NetworkCpsManager::getInstance();
                $networkCpsManager->addCps($player);
                if($networkCpsManager->getCps($player) > 20) {
                    $session->addAutoViolation();
                    Util::getInstance()->log($this->getFlagId(), $player, $session->getAutoViolations(), $networkCpsManager->getCps($player));
                    $this->checkAlerts($player);
                }
            }
        }
    }

    /**
     * @priority HIGHEST
     * @throws Exception
     */

    public function cps2(PlayerMissSwingEvent $event) : void { //when a player attempts to perform the attack action without a target entity
        $player = $event->getPlayer();
        $session = SessionManager::getInstance()->getSession($player);
        $networkCpsManager = NetworkCpsManager::getInstance();

        if($networkCpsManager->getCps($player) > 20) {
            $session->addAutoViolation();
            Util::getInstance()->log($this->getFlagId(), $player, $session->getAutoViolations(), $networkCpsManager->getCps($player));
            $this->checkAlerts($player);
        }
    }

    public function checkAlerts(Player $player): void {
        $session = SessionManager::getInstance()->getSession($player);
        if(!$session instanceof Session) {
            return;
        }

        if($session->getAutoViolations() > 50) {
            ModuleUtil::getInstance()->ban($player, Translator::translateModule($this->getFlagId()));
        }
    }

}
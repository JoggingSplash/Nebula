<?php

namespace bitpvp\module\preset;

use bitpvp\module\IModule;
use bitpvp\session\Session;
use bitpvp\session\SessionManager;
use bitpvp\util\ModuleUtil;
use bitpvp\util\types\Translator;
use bitpvp\util\Util;
use Exception;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class Reach extends IModule implements Listener {

    public function __construct() {
        parent::__construct(self::REACH);
    }


    /**
     * @throws Exception
     */
    public function reach(EntityDamageByEntityEvent $event): void {
        $player = $event->getEntity();
        $cause = $event->getCause();

        if ($event instanceof EntityDamageByChildEntityEvent) {
            return;
        }

        if ($cause !== EntityDamageEvent::CAUSE_ENTITY_ATTACK) {
            return;
        }

        $damager = $event->getDamager();

        if (!$player instanceof Player && !$damager instanceof Player) {
            return;
        }

        $session = SessionManager::getInstance()->getSession($damager);

        if (!$session instanceof Session) {
            return;
        }

        if($damager->isCreative()){
            return;
        }
        $damagerPing = $damager->getNetworkSession()->getPing();
        $playerPing = $player->getNetworkSession()->getPing();

        $distance = $player->getEyePos()->distance(new Vector3($damager->getEyePos()->getX(), $player->getEyePos()->getY(), $damager->getEyePos()->getZ()));
        $distance -= $damagerPing * 0.0043;
        $distance -= $playerPing * 0.0053;

        if ($distance < 1) {
            return;
        }

        if ($player->isSprinting()) {
            $distance -= 0.97;
        } else {
            $distance -= 0.87;
        }

        if ($damager->isSprinting()) {
            $distance -= 0.78;
        } else {
            $distance -= 0.68;
        }

        if ($distance > 5.7) {
            $event->cancel();
            return;
        }

        if ($distance > 3) {
            $detail = round($distance, 3);
            $session->addReachViolation();
            Util::getInstance()->log($this->getFlagId(), $damager, $session->getReachViolations(), $detail);
            if($session->getReachViolations() > 25) {
                ModuleUtil::getInstance()->ban($damager, Translator::translateModule($this->getFlagId()));
            }
        }
    }
}
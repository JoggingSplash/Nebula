<?php

declare(strict_types=1);

namespace bitpvp\module\preset;

use bitpvp\module\IModule;
use bitpvp\session\Session;
use bitpvp\session\SessionManager;
use bitpvp\util\ModuleUtil;
use bitpvp\util\types\Translator;
use bitpvp\util\Util;
use Exception;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\player\Player;
use function microtime;

class Timer extends IModule implements Listener {

	public function __construct() {
		parent::__construct(self::TIMER);
	}

    /**
     * @throws Exception
     * @priority HIGHEST
     */
    public function verifyTimer(Player $player): void {
        if (!$player->isConnected()) {
            return;
        }

        $session = SessionManager::getInstance()->getSession($player);

        if(!$session instanceof Session) {
            return;
        }

        if ($session->isWaitTime()) {
            return;
        }

        if (!$player->isAlive()) {
            $session->timerLastTimestamp = -1.0;
            return;
        }

        $timestamp = microtime(true);

        if ($session->timerLastTimestamp === -1.0) {
            $session->timerLastTimestamp = $timestamp;
            return;
        }

        $diff = $timestamp - $session->timerLastTimestamp;

        $session->timerBalance += 0.05;
        $session->timerBalance -= $diff;

        if ($session->timerBalance >= 0.25) {
            $session->timerBalance = 0.0;

            if (time() - $session->timerWait < 1) {
                return;
            }

            $session->timerWait = time();
            $session->addTimerViolation();
            Util::getInstance()->log($this->getFlagId(), $player, $session->getTimerViolations(), round($diff, 3));

            if($session->getTimerViolations() > 14) {
                ModuleUtil::getInstance()->ban($player, Translator::translateModule($this->getFlagId()));
            }
        }

        $session->timerLastTimestamp = $timestamp;
    }


    /**
     * @throws Exception
     * @priority HIGHEST
     */
    public function timer(DataPacketReceiveEvent $event): void {
        $player = $event->getOrigin()->getPlayer();
        $packet = $event->getPacket();
        if ($player instanceof Player and $player->isConnected() and $packet instanceof PlayerAuthInputPacket) {
            if (!$player->spawned or !$player->isSurvival()) {
                return;
            }
            $this->verifyTimer($player);
        }
    }

}
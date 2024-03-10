<?php

declare(strict_types=1);

namespace bitpvp\module\preset;


use bitpvp\module\IModule;
use bitpvp\network\NetworkStackLatencyManager;
use bitpvp\session\Session;
use bitpvp\session\SessionManager;
use bitpvp\util\ModuleUtil;
use bitpvp\util\types\Translator;
use bitpvp\util\Util;
use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\NetworkStackLatencyPacket;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\SetActorMotionPacket;
use pocketmine\network\mcpe\protocol\types\PlayerAction;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;
use pocketmine\network\mcpe\protocol\types\PlayerBlockActionWithBlockInfo;
use pocketmine\player\Player;
use Exception;

class Velocity extends IModule implements Listener {

    public function __construct() {
        parent::__construct(self::VELOCITY);
    }

    public function velocity(DataPacketReceiveEvent $event): void {
        $player = $event->getOrigin()->getPlayer();
        $packet = $event->getPacket();

        if ($packet instanceof PlayerAuthInputPacket and $player instanceof Player and $player->isConnected()) {
            if(!$player->spawned or !$player->isSurvival()){
                return;
            }
            $session = SessionManager::getInstance()->getSession($player);

            if(!$session instanceof Session){
                return;
            }

            if($player->isUnderwater()){
                return;
            }

            if(!is_int($player->getNetworkSession()->getPing())) {
                return;
            }

            $blockAbove = $player->getWorld()->getBlockAt($player->getPosition()->getFloorX(), $player->getPosition()->getFloorY() + 2, $player->getPosition()->getFloorZ());

            if($blockAbove instanceof Block and !$blockAbove instanceof Air) {
                return;
            }

            if (is_null($session->lastLocation)) {
                $session->lastLocation = $packet->getPosition()->subtract(0, 1.62, 0);
                $session->vMotion = null;
                return;
            }
            $motion = ($session->vMotion ?? null);
            if($motion !== null) {
                $movementY = $packet->getPosition()->subtract(0, 1.62, 0)->getY() - $session->lastLocation->getY();
                if (!$player->isAlive() or $player->hasNoClientPredictions()) {
                    $session->vMotion = 0;
                    return;
                }
                if($motion > 0.005) {
                    $percentage = ($movementY / $motion);
                    if ($percentage < 0.9999 and $percentage > 0.01) {
                        if (time() - $session->velocityWait < 1) {
                            return;
                        }
                        $session->velocityWait = time();
                        $session->addVelocityViolation();
                        Util::getInstance()->log($this->getFlagId(), $player, $session->getVelocityViolations(), round($percentage, 3));

                        if($session->getVelocityViolations() > 12) {
                            ModuleUtil::getInstance()->ban($player, Translator::translateModule($this->getFlagId()));
                        }
                    }
                    $session->vMotion -= 0.08;
                    $session->vMotion *= 0.98000001907349;
                } else {
                    $session->vMotion = null;
                }
            }
            $session->lastLocation = $packet->getPosition()->subtract(0, 1.62, 0);
        } elseif ($packet instanceof NetworkStackLatencyPacket) {
            NetworkStackLatencyManager::getInstance()->execute($player, $packet->timestamp);
        }
    }
    /**
     * @throws Exception
     * @priority HIGHEST
     */
    public function handleReceivePacket(DataPacketReceiveEvent $event): void {
        $origin = $event->getOrigin();
        $player = $origin->getPlayer();
        if ($player instanceof Player and $player->isConnected()) {
            $packet = $event->getPacket();
            if ($packet instanceof PlayerAuthInputPacket and is_null($origin->getHandler())) {
                $pkPos = $packet->getPosition();
                foreach ([$pkPos->x, $pkPos->y, $pkPos->z, $packet->getYaw(), $packet->getHeadYaw(), $packet->getPitch()] as $float) {
                    if (is_infinite($float) || is_nan($float)) {
                        return;
                    }
                }
                $pos = $player->getLocation();
                $distanceSquared = $pkPos->round(4)->subtract(0, 1.62, 0)->distanceSquared($player->getPosition());

                if ($packet->getYaw() - $pos->getYaw() !== 0.0 || $packet->getPitch() - $pos->getPitch() !== 0.0 || $distanceSquared !== 0.0) {
                    $origin->getHandler()->handleMovePlayer(MovePlayerPacket::simple($player->getId(), $pkPos, $packet->getPitch(), $packet->getYaw(), $packet->getHeadYaw(), MovePlayerPacket::MODE_NORMAL, false, 0, $packet->getTick()));
                }

                if ($packet->getItemInteractionData() !== null) {
                    $data = $packet->getItemInteractionData();
                    $origin->getHandler()->handleInventoryTransaction(InventoryTransactionPacket::create($data->getRequestId(), $data->getRequestChangedSlots(), $data->getTransactionData()));
                }
                if ($packet->getBlockActions() !== null) {
                    foreach ($packet->getBlockActions() as $blockAction) {
                        $actionType = match ($blockAction->getActionType()) {
                            PlayerAction::CONTINUE_DESTROY_BLOCK => PlayerAction::START_BREAK,
                            PlayerAction::PREDICT_DESTROY_BLOCK => PlayerAction::STOP_BREAK,
                            default => $blockAction->getActionType()
                        };
                        if ($blockAction instanceof PlayerBlockActionWithBlockInfo) {
                            $origin->getHandler()->handlePlayerAction(PlayerActionPacket::create($player->getId(), $actionType, $blockAction->getBlockPosition(), $blockAction->getBlockPosition(), $blockAction->getFace())); //TODO: Find out what $resultPosition is ($blockAction->getBlockPosition())
                        }
                    }
                }

                if ($packet->hasFlag(PlayerAuthInputFlags::START_SPRINTING)) {
                    if (!$player->toggleSprint(true)) {
                        $player->sendData([$player]);
                    }
                }
                if ($packet->hasFlag(PlayerAuthInputFlags::STOP_SPRINTING)) {
                    if (!$player->toggleSprint(false)) {
                        $player->sendData([$player]);
                    }
                }
                if ($packet->hasFlag(PlayerAuthInputFlags::START_SNEAKING)) {
                    if (!$player->toggleSneak(true)) {
                        $player->sendData([$player]);
                    }
                }
                if ($packet->hasFlag(PlayerAuthInputFlags::STOP_SNEAKING)) {
                    if (!$player->toggleSneak(false)) {
                        $player->sendData([$player]);
                    }
                }
            }
        }
    }

    /**
     * @throws Exception
     * @priority HIGHEST
     */

    public function handleSendPacket(DataPacketSendEvent $event): void{
        foreach ($event->getTargets() as $targets) {
            if (!$targets instanceof Player or !$targets->isConnected()){
                return;
            }
            $session = SessionManager::getInstance()->getSession($targets);

            if(!$session instanceof Session){
                return;
            }

            foreach ($event->getPackets() as $packet) {
                $packet = PacketPool::getInstance()->getPacket($packet->getName());
                if ($packet instanceof SetActorMotionPacket) {
                    if ($packet->actorRuntimeId === $targets->getId()) {
                        $motion = $packet->motion->getY();
                        NetworkStackLatencyManager::getInstance()->send($targets, function () use ($session, $targets, $motion): void {
                            $session->vMotion = $motion;
                        });
                    }
                }
            }
        }
    }


}

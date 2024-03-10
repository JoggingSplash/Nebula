<?php

namespace bitpvp\module\preset;

use bitpvp\module\IModule;
use bitpvp\session\Session;
use bitpvp\session\SessionManager;
use bitpvp\util\ModuleUtil;
use bitpvp\util\types\Translator;
use bitpvp\util\Util;
use Exception;
use pocketmine\block\BlockTypeIds;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class Fly extends IModule implements Listener {

    public function __construct() {
        parent::__construct(self::FLY);
    }

    /**
     * @priority LOW
     * @throws Exception
     */

    public function fly(PlayerMoveEvent $event) : void {
        $player = $event->getPlayer();
        $oldPos = $event->getFrom();
        $newPos = $event->getTo();

        $session = SessionManager::getInstance()->getSession($player);

        if (!$session instanceof Session) {
            return;
        }

        if ($player->hasNoClientPredictions()) {
            return;
        }

        if ($player->isCreative() or $player->isSpectator() or $player->getAllowFlight()) {
            return;
        }

        $surroundingBlocks = $this->GetSurroundingBlocks($player);

        if ($oldPos->getY() <= $newPos->getY()) {
            if ($player->getInAirTicks() > 90 && !$session->isGettingDamage) {
                $maxY = $player->getWorld()->getHighestBlockAt(intval($newPos->getX()), intval($newPos->getZ()));
                if ($newPos->getY() - 2 > $maxY) {
                    if (!in_array(BlockTypeIds::OAK_FENCE, $surroundingBlocks, true) || !in_array(BlockTypeIds::COBBLESTONE_WALL, $surroundingBlocks, true) || !in_array(BlockTypeIds::ACACIA_FENCE, $surroundingBlocks, true) || !in_array(BlockTypeIds::OAK_FENCE, $surroundingBlocks, true) || !in_array(BlockTypeIds::BIRCH_FENCE, $surroundingBlocks, true) || !in_array(BlockTypeIds::DARK_OAK_FENCE, $surroundingBlocks, true) || !in_array(BlockTypeIds::JUNGLE_FENCE, $surroundingBlocks, true) || !in_array(BlockTypeIds::NETHER_BRICK_FENCE, $surroundingBlocks, true) || !in_array(BlockTypeIds::SPRUCE_FENCE, $surroundingBlocks, true) || !in_array(BlockTypeIds::WARPED_FENCE, $surroundingBlocks, true) || !in_array(BlockTypeIds::MANGROVE_FENCE, $surroundingBlocks, true) || !in_array(BlockTypeIds::CRIMSON_FENCE, $surroundingBlocks, true) || !in_array(BlockTypeIds::CHERRY_FENCE, $surroundingBlocks, true) || !in_array(BlockTypeIds::ACACIA_FENCE_GATE, $surroundingBlocks, true) || !in_array(BlockTypeIds::OAK_FENCE_GATE, $surroundingBlocks, true) || !in_array(BlockTypeIds::BIRCH_FENCE_GATE, $surroundingBlocks, true) || !in_array(BlockTypeIds::DARK_OAK_FENCE_GATE, $surroundingBlocks, true) || !in_array(BlockTypeIds::JUNGLE_FENCE_GATE, $surroundingBlocks, true) || !in_array(BlockTypeIds::SPRUCE_FENCE_GATE, $surroundingBlocks, true) || !in_array(BlockTypeIds::WARPED_FENCE_GATE, $surroundingBlocks, true) || !in_array(BlockTypeIds::MANGROVE_FENCE_GATE, $surroundingBlocks, true) || !in_array(BlockTypeIds::CRIMSON_FENCE_GATE, $surroundingBlocks, true) || !in_array(BlockTypeIds::CHERRY_FENCE_GATE, $surroundingBlocks, true) || !in_array(BlockTypeIds::GLASS_PANE, $surroundingBlocks, true) || !in_array(BlockTypeIds::HARDENED_GLASS_PANE, $surroundingBlocks, true) || !in_array(BlockTypeIds::STAINED_GLASS_PANE, $surroundingBlocks, true) ||  !in_array(BlockTypeIds::STAINED_HARDENED_GLASS_PANE, $surroundingBlocks, true) ) { // yea fuck that LOLLLLLLLL
                        $session->addFlyViolation();
                        Util::getInstance()->log($this->getFlagId(), $player, $session->flyViolations, 1);
                        if($session->flyViolations > 40) {
                            ModuleUtil::getInstance()->ban($player, Translator::translateModule($this->getFlagId()));
                        }
                    }
                }
            }
        }
    }

    /**
     * @priority HIGHEST
     * @throws Exception
     */
    public function gettingDamage(EntityDamageEvent $event) : void {
        $player = $event->getEntity();

        if(!$player instanceof Player){
            return;
        }

        $sessionPlayer = SessionManager::getInstance()->getSession($player);

        if (!$sessionPlayer instanceof Session) {
            return;
        }

        $c = $event->getCause();

        if($c === EntityDamageEvent::CAUSE_ENTITY_ATTACK or $c === EntityDamageEvent::CAUSE_PROJECTILE) {
            $sessionPlayer->isGettingDamage = true;
            return;
        }

        $sessionPlayer->isGettingDamage = false;
    }

    public function GetSurroundingBlocks(Player $player) : array {
        $world = $player->getWorld();

        $posX = $player->getLocation()->getX();
        $posY = $player->getLocation()->getY();
        $posZ = $player->getLocation()->getZ();

        $pos1 = new Vector3($posX  , $posY, $posZ  );
        $pos2 = new Vector3($posX - 1, $posY, $posZ  );
        $pos3 = new Vector3($posX - 1, $posY, $posZ - 1);
        $pos4 = new Vector3($posX  , $posY, $posZ - 1);
        $pos5 = new Vector3($posX + 1, $posY, $posZ  );
        $pos6 = new Vector3($posX + 1, $posY, $posZ + 1);
        $pos7 = new Vector3($posX  , $posY, $posZ + 1);
        $pos8 = new Vector3($posX + 1, $posY, $posZ - 1);
        $pos9 = new Vector3($posX - 1, $posY, $posZ + 1);

        $bpos1 = $world->getBlock($pos1)->getTypeId();
        $bpos2 = $world->getBlock($pos2)->getTypeId();
        $bpos3 = $world->getBlock($pos3)->getTypeId();
        $bpos4 = $world->getBlock($pos4)->getTypeId();
        $bpos5 = $world->getBlock($pos5)->getTypeId();
        $bpos6 = $world->getBlock($pos6)->getTypeId();
        $bpos7 = $world->getBlock($pos7)->getTypeId();
        $bpos8 = $world->getBlock($pos8)->getTypeId();
        $bpos9 = $world->getBlock($pos9)->getTypeId();

        return  [$bpos1, $bpos2, $bpos3, $bpos4, $bpos5, $bpos6, $bpos7, $bpos8, $bpos9];
    }
}
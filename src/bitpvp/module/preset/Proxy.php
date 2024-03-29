<?php

namespace bitpvp\module\preset;

use bitpvp\module\IModule;
use bitpvp\util\ModuleUtil;
use bitpvp\util\types\Translator;
use bitpvp\util\Util;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\player\Player;

class Proxy extends IModule implements Listener {

    public function __construct() {
        parent::__construct(self::PROXY);
    }

    /**
     * @priority NORMAL
     * @ignoreCancelled TRUE
     */
    public function windows(PlayerJoinEvent $event) { // default device spoofer == android
        $player = $event->getPlayer();

        if ($player instanceof Player) {

            $deviceOS = (int)$player->getPlayerInfo()->getExtraData()["DeviceOS"];
            $deviceModel = (string)$player->getPlayerInfo()->getExtraData()["DeviceModel"];

            if ($deviceOS !== 1) {
                return;
            }

            $name = explode(" ", $deviceModel);

            if (!isset($name[0]))  {
                return;
            }

            $check = $name[0];
            $check = strtoupper($check);
            if ($check !== $name[0]) {
                ModuleUtil::getInstance()->ban($player, Translator::translateModule($this->getFlagId()));
                Util::getInstance()->log($this->getFlagId(), $player, 1 , 0);
            }
        }
    }
}
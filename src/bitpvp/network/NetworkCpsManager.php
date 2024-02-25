<?php

namespace bitpvp\network;

use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
final class NetworkCpsManager {
    use SingletonTrait;
    public array $clicks = [];

    public function addCps(Player $player) : void {
        if (empty($this->clicks[$player->getUniqueId()->getBytes()])) {
            $this->clicks[$player->getUniqueId()->getBytes()] = [];
        }
        array_unshift($this->clicks[$player->getUniqueId()->getBytes()], microtime(true));
        if (count($this->clicks[$player->getUniqueId()->getBytes()]) >= 100) {
            array_pop($this->clicks[$player->getUniqueId()->getBytes()]);
        }
    }

    public function getCps(Player $player) : float {
        if (empty($this->clicks[$player->getUniqueId()->getBytes()])) {
            return 0.0;
        }
        $ct = microtime(true);
        return round(count(array_filter($this->clicks[$player->getUniqueId()->getBytes()], static function(float $t) use ($ct) : bool {
                return ($ct - $t) <= 1.0;
            })) / 1.0, 1);
    }
}
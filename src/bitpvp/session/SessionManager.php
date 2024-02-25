<?php

declare(strict_types=1);

namespace bitpvp\session;

use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

class SessionManager {
    use SingletonTrait;

    /** @var Session[]  */
    private array $sessions = [];

    public function getSessions(): array {
        return $this->sessions;
    }

    public function getSession(Player $player): ?Session {
        return $this->sessions[$player->getName()] ?? null;
    }

    public function createSession(Player $player): void {
        $this->sessions[$player->getName()] = new Session($player);;
    }

    public function removeSession(Player $player): void {
        if (!($this->getSession($player)) instanceof Session) {
            return;
        }
        unset($this->sessions[$player->getName()]);
    }
}
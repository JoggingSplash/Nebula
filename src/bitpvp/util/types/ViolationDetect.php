<?php

declare(strict_types=1);

namespace bitpvp\util\types;

use bitpvp\session\Session;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;

class ViolationDetect extends Task {

    public function __construct(
        private readonly Session $session,
        private readonly Player $player
    ) {}

    public function onRun(): void {
        if (!$this->player->isConnected()) {
            $this->getHandler()->cancel();
            return;
        }

        if ($this->session->reachViolations > 0) {
            $this->session->reachViolations = 0;
        }
        if ($this->session->timerViolations > 0) {
            $this->session->timerViolations = 0;
        }
        
        if ($this->session->velocityViolations > 0) {
            $this->session->velocityViolations = 0;
        }

        if($this->session->flyViolations > 0 ) {
            $this->session->flyViolations = 0;
        }

        if($this->session->autoViolations > 0) {
            $this->session->autoViolations = 0;
        }

        if($this->session->packetsViolations > 0) {
            $this->session->packetsViolations = 0;
        }
    }
}
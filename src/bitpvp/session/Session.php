<?php

declare(strict_types=1);

namespace bitpvp\session;

use bitpvp\Nebula;
use bitpvp\util\types\ViolationDetect;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class Session
{
    public ?float $vMotion = null;
    public Vector3|float|null $lastLocation = null;
    public float $timerLastTimestamp = -1.0;
    public float $timerBalance = 0.0;
    public int $timerWait = -1;
    public int $velocityWait = -1;
    public int $reachViolations = 0;
    public int $timerViolations = 0;
    public int $velocityViolations = 0;
    public int $flyViolations = 0;
    public int $autoViolations = 0;
    public int $packetsDelay = 0;
    public int $packetsViolations = 0;
    public bool $enableAlerts = false;
    public bool $waitTime = true;
    public bool $isGettingDamage = false;

    public function __construct(
        public Player $player
    ) {}

    /** @var Session[]  */
    private array $sessions = [];

    public function onJoin(): void {
        Nebula::getInstance()->getScheduler()->scheduleRepeatingTask(new ViolationDetect($this, $this->player), 20 * 60);
    }

    public function addReachViolation(): void {
        $this->reachViolations++;
    }

    public function addTimerViolation(): void {
        $this->timerViolations++;
    }

    public function addVelocityViolation(): void {
        $this->velocityViolations++;
    }

    public function addFlyViolation(): void {
		$this->flyViolations++;
	}

    public function addAutoViolation() : void {
        $this->autoViolations++;
    }

    public function addPacketsViolation(): void {
        $this->packetsViolations++;
    }

	public function setEnableAlerts(bool $enableAlerts = true): void {
		$this->enableAlerts = $enableAlerts;
	}

	public function setWaitTime(bool $waitTime): void {
		$this->waitTime = $waitTime;
	}

	public function isEnableAlerts(): bool {
		return $this->enableAlerts;
	}

	public function isWaitTime(): bool {
		return $this->waitTime;
	}
}
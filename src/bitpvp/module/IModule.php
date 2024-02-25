<?php

declare(strict_types=1);

namespace bitpvp\module;

abstract class IModule {

    public const REACH = 0;
    public const TIMER = 1;
    public const VELOCITY = 2;
    public const FLY = 3;
    public const AUTOCLICK = 4;
    public const PACKETS = 5;
    public const PROXY = 6;


    public function __construct(
        private readonly int $flagId
    ) {}

    public function getFlagId(): int {
        return $this->flagId;
    }
}
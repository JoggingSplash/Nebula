<?php

namespace bitpvp\util;

use bitpvp\module\IModule;
use bitpvp\Nebula;
use bitpvp\registry\Registry;
use bitpvp\session\SessionManager;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;

class ModuleUtil {
    use SingletonTrait;
    public function formatViolationMessage(int $flagId, string $player, int $ping, int $violations, string $deviceOS, float|int $details = 0.0): ?string {
        return match ($flagId) {
            IModule::REACH => TextFormat::colorize("&7[&4!&7] &5[Nebula] &7 $player violated &4Reach &7[distance: $details] [ping: $ping] [Device: $deviceOS] [x$violations]"),
            IModule::TIMER => TextFormat::colorize("&7[&4!&7] &5[Nebula] &7$player violated &4Timer &7[difference: $details] [ping: $ping] [Device: $deviceOS] [x$violations]"),
            IModule::VELOCITY => TextFormat::colorize("&7[&4!&7] &5[Nebula] &7$player violated &4Velocity &7[amount: $details] [ping: $ping] [Device: $deviceOS] [x$violations]"),
            IModule::FLY => TextFormat::colorize("&7[&4!&7] &5[Nebula] &7$player violated &4Fly &7[ping: $ping] [Device: $deviceOS] [x$violations]"),
            IModule::AUTOCLICK => TextFormat::colorize("&7[&4!&7] &5[Nebula] &7$player violated &4AutoClick &7[cps: $details] [ping: $ping] [Device: $deviceOS] [x$violations]"),
            IModule::PACKETS => TextFormat::colorize("&7[&4!&7] &5[Nebula] &7$player violated &4Packets &7[ping: $ping] [Device: $deviceOS] [x$violations]"),
            IModule::PROXY => TextFormat::colorize("&7[&4!&7] &5[Nebula] &7$player tried to join using &4Proxy &7[ping: $ping] [Device: $deviceOS]"),
            default => null
        };
    }

    public function ban(Player $player, string $reason): void {

        Nebula::getInstance()->getServer()->getPlayerByPrefix($player->getName())->kick(TextFormat::colorize("\n" . "&cNebula found you using ". $reason));

        if(Util::getInstance()->getConfig("ban")) {
            Registry::getInstance()->save($player->getName(), $reason, "Never");
            Nebula::getInstance()->getServer()->broadcastMessage(TextFormat::colorize("&l&5[Nebula] &r&5{$player->getName()} was found cheating using &c$reason &5and was removed permanently from the Server" . "\n"));
            Util::getInstance()->logger($player, $reason);
            return;
        }
        Nebula::getInstance()->getServer()->broadcastMessage(TextFormat::colorize("&l&5[Nebula] &r&5{$player->getName()} was found cheating using &c$reason &5and was removed from the Server" . "\n"));

    }
}
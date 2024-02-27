<?php

namespace bitpvp\util;

use bitpvp\Nebula;
use bitpvp\session\Session;
use bitpvp\session\SessionManager;
use bitpvp\util\types\Translator;
use Juqn\CortexPE\DiscordWebhookAPI\Embed;
use Juqn\CortexPE\DiscordWebhookAPI\Message;
use Juqn\CortexPE\DiscordWebhookAPI\Webhook;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;

class Util {
    use SingletonTrait;

    public function getConfig(string $getter): mixed {
        return Nebula::getInstance()->getConfig()->get($getter);
    }

    public function log(int $flagId, Player $player, int $violations, int|float $details): void {
        $message = ModuleUtil::getInstance()->formatViolationMessage($flagId, $player->getName(), $player->getNetworkSession()->getPing(), $violations, Translator::translateDevice($player->getPlayerInfo()->getExtraData()['DeviceOS']), $details);
        foreach (Nebula::getInstance()->getServer()->getOnlinePlayers() as $players) {
            $sessions = SessionManager::getInstance()->getSession($players);

            if(!$sessions instanceof Session) {
                Nebula::getInstance()->getServer()->getLogger()->debug("Error Loading {$players->getName()} session");
                return;
            }

            if($players->hasPermission("nebula.ac") && $sessions->isEnableAlerts()) {
                $players->sendMessage($message);
            }
        }
        Webhook::create($this->getConfig('webhook-alerts'))
            ->send(Message::create()
                ->addEmbed(Embed::create()
                    ->setTitle('[Nebula]')
                    ->setColor(0xFF8000)
                    ->setDescription(TextFormat::clean($message))
                )
            );

        Nebula::getInstance()->getServer()->getLogger()->info(TextFormat::clean($message));
    }

    public function logger(string $player, string $reason): void {
        $message = "{$player} was found cheating using $reason";

        Webhook::create($this->getConfig('webhook-ban'))
            ->send(Message::create()
                ->addEmbed(Embed::create()
                    ->setTitle('[Nebula]')
                    ->setColor(0xFF8000)
                    ->setDescription(TextFormat::clean($message))
                )
            );
    }
}
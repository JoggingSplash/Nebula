<?php

declare(strict_types=1);

namespace bitpvp;

use bitpvp\command\Info;
use bitpvp\command\Pardon;
use bitpvp\module\preset\Autoclick;
use bitpvp\module\preset\Proxy;
use bitpvp\module\preset\Packets;
use bitpvp\module\preset\Reach;
use bitpvp\module\preset\Timer;
use bitpvp\module\preset\Velocity;
use bitpvp\module\preset\Fly;
use bitpvp\registry\Registry;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use bitpvp\command\Alerts;

class Nebula extends PluginBase {
    use SingletonTrait;

	protected function onLoad() : void {
		self::setInstance($this);
	}

	protected function onEnable() : void {
        $this->getLogger()->info(TextFormat::DARK_PURPLE . "[+]");

        if (($command = $this->getServer()->getCommandMap()->getCommand("pardon")) !== null) {
            $this->getServer()->getCommandMap()->unregister($command);
        }

        $this->getServer()->getCommandMap()->register("nebula", new Alerts());
        $this->getServer()->getCommandMap()->register("nebula", new Info());
        $this->getServer()->getCommandMap()->register("nebula", new Pardon());

        $this->saveDefaultConfig();

        $this->registerListeners(
            new Velocity(),
            new Reach(),
            new Timer(),
            new Fly(),
            new Autoclick(),
            new Packets(),
            new Proxy(),
            new EventHandler()
        );
	}

    protected function onDisable(): void {
        $this->getLogger()->notice(TextFormat::DARK_PURPLE . "[-]");
        Registry::getInstance()->quit();
    }

    public function registerListeners(Listener ...$handlers): void {
        foreach ($handlers as $handler) {
            $this->getServer()->getPluginManager()->registerEvents($handler, $this);
        }
    }
}
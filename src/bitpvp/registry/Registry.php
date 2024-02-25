<?php

namespace bitpvp\registry;

use bitpvp\Nebula;
use pocketmine\utils\SingletonTrait;

class Registry
{
    use SingletonTrait;

    private \SQLite3 $registry;

    public function __construct() {
        $this->registry = new \SQLite3(Nebula::getInstance()->getDataFolder() . 'nebula.db');
        $this->registry->exec("CREATE TABLE IF NOT EXISTS users(username TEXT PRIMARY KEY, reason TEXT, expires TEXT);");
    }

    /**
     * @return \SQLite3
     */
    public function db(): \SQLite3  {
        return $this->registry;
    }

    public function exists(string $username): bool {
        $result = $this->registry->querySingle("SELECT EXISTS(SELECT 1 FROM users WHERE username = '$username')");
        return (bool)$result;
    }

    public function save(string $username, string $reason, string $expire): void {
        $this->registry->exec("INSERT OR REPLACE INTO users(username, reason, expires) VALUES ('$username', '$reason', '$expire');");
    }

    public function quit(): void {
        $this->registry->close();
    }
}
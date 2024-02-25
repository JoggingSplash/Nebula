<?php

/**
 *
 *  _____      __    _   ___ ___
 * |   \ \    / /__ /_\ | _ \_ _|
 * | |) \ \/\/ /___/ _ \|  _/| |
 * |___/ \_/\_/   /_/ \_\_| |___|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * Written by @CortexPE <https://CortexPE.xyz>
 * Intended for use on SynicadeNetwork <https://synicade.com>
 */

declare(strict_types=1);

namespace CortexPE\DiscordWebhookAPI\task;


use CortexPE\DiscordWebhookAPI\Message;
use CortexPE\DiscordWebhookAPI\Webhook;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class DiscordWebhookSendTask extends AsyncTask {

    private readonly string $url;
    private readonly mixed $message;
    private readonly bool $hasFile;

    public function __construct(
        Webhook $webhook,
        Message $message
    ) {
        $this->url = $webhook->getURL();
        $this->hasFile = $message->hasFile();

        if ($message->hasFile()) {
            $this->message = $message->jsonSerialize();
        } else {
            $this->message = json_encode($message);
        }
    }

    public function onRun(): void {
        $result = $this->send($this->url, $this->message, $this->hasFile);
        $this->setResult($result);
    }

    public function onCompletion(): void {
        $response = $this->getResult();

        if (in_array($response[1], [200, 204])) {
            return;
        }
        Server::getInstance()->getLogger()->error('[DiscordWebhookAPI] Got error (' . $response[1] . '): ' . $response[0]);
    }

    private function send(string $url, mixed $message, bool $hasFile): array {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 400);
        curl_setopt($ch, CURLOPT_NOSIGNAL, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $message);

        if (!$hasFile) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
        }
        $ret = [curl_exec($ch), curl_getinfo($ch, CURLINFO_RESPONSE_CODE)];
        curl_close($ch);

        return $ret;
    }
}
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

declare(strict_types = 1);

namespace Juqn\CortexPE\DiscordWebhookAPI;

use JsonSerializable;

class Message implements JsonSerializable {

    protected array $data = [];

    public static function create(): Message {
        return new Message();
    }

    public function setContent(string $content): Message {
        $this->data['content'] = $content;
        return $this;
    }

    public function getContent(): ?string {
        return $this->data['content'] ?? null;
    }

    public function getUsername(): ?string {
        return $this->data['username'];
    }

    public function setUsername(string $username): Message {
        $this->data['username'] = $username;
        return $this;
    }

    public function getAvatarURL(): ?string {
        return $this->data['avatar_url'] ?? null;
    }

    public function setAvatarURL(string $avatarURL): Message {
        $this->data['avatar_url'] = $avatarURL;
        return $this;
    }

    public function addEmbed(Embed $embed): Message {
        if (!empty(($arr = $embed->asArray()))) {
            $this->data['embeds'][] = $arr;
        }
        return $this;
    }

    public function setTextToSpeech(bool $ttsEnabled): Message {
        $this->data['tts'] = $ttsEnabled;
        return $this;
    }

    public function jsonSerialize(): array {
        return $this->data;
    }

    public function attachFile(string $fileName, ?string $mimeType = null, ?string $postedFileName = null): Message {
        $this->data['file'] = curl_file_create($fileName, $mimeType, $postedFileName);
        return $this;
    }

    public function hasFile(): bool {
        return isset($this->data['file']);
    }
}
<?php

namespace bitpvp\util\types;


use pocketmine\network\mcpe\protocol\types\DeviceOS;
use pocketmine\network\mcpe\protocol\types\InputMode;
use bitpvp\module\IModule;

final class Translator {

    public static function translateDevice(int $device): string {
        return match ($device){
            DeviceOS::ANDROID => "Android",
            DeviceOS::IOS => "IOS",
            DeviceOS::OSX => "Osx",
            DeviceOS::AMAZON => "Amazon",
            DeviceOS::GEAR_VR => "Gear VR",
            DeviceOS::HOLOLENS => "Hololens",
            DeviceOS::WINDOWS_10 => "Win10",
            DeviceOS::WIN32 => "Win32",
            DeviceOS::DEDICATED => "Dedicated",
            DeviceOS::TVOS => "TvOS",
            DeviceOS::NINTENDO => "Nintendo",
            DeviceOS::PLAYSTATION => "PlayStation",
            DeviceOS::XBOX => "Xbox",
            DeviceOS::WINDOWS_PHONE => "Windows Phone",
            default => "Unknown"
        };
    }

    public static function translateInputMode(int $inputDevice): string {
        return match($inputDevice){
            InputMode::GAME_PAD => "Game Pad",
            InputMode::MOTION_CONTROLLER => "Controller",
            InputMode::MOUSE_KEYBOARD => "KeyBoard",
            InputMode::TOUCHSCREEN => "Touch",
            default => "Unknown"
        };
    }

    public static function translateModule(int $module): string {
        return match ($module){
            IModule::REACH => "Reach",
            IModule::TIMER => "Timer",
            IModule::VELOCITY => "Velocity",
            IModule::FLY => "Fly",
            IModule::AUTOCLICK => "AutoClick +20 CPS",
            IModule::PACKETS => "BadPackets",
            IModule::PROXY => "Proxy"
        };
    }

}
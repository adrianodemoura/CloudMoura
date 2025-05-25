<?php

namespace CloudMoura\Config;

class SmsDev {
    public static function getConfig(): array
    {
        return [
            'smsdev_key' => $_ENV['SMSDEV_API_KEY']
        ];
    }
}

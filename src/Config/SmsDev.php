<?php

namespace CloudMoura\Config;

class SmsDev {
    public static function getConfig(): array
    {
        return [
            'smsdev_key' => isset( $_ENV['SMSDEV_API_KEY'] ) ? $_ENV['SMSDEV_API_KEY'] : ''
        ];
    }
}

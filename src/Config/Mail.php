<?php

namespace CloudMoura\Config;

class Mail {
    /**
     * Get the configuration for the mail server.
     *
     * @return array
     */
    public static function getConfig(): array {

        return [
            'host'       => $_ENV['MAIL_HOST'],
            'username'   => $_ENV['MAIL_USER'],
            'password'   => $_ENV['MAIL_PASSWORD_APP'],
            'port'       => $_ENV['MAIL_PORT'],
            'smtp_secure'=> $_ENV['MAIL_SECURE'],
            'from'       => $_ENV['MAIL_FROM'],
        ];
    }
}
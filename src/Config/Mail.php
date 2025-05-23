<?php

namespace CloudMoura\Config;

class Mail {
    public static function getConfig(): array
    {
        return [
            'host'       => 'smtp.gmail.com',
            'username'   => 'tvmoura.aitt@gmail.com',      // Altere aqui
            'password'   => 'Debiano6702@',       // Altere aqui
            "app_password" => 'cnnp ibsx pohy yyfs', // Altere aqui
            'port'       => 587,
            'smtp_secure'=> 'tls',
            'from'       => 'tvmoura.aitt@gmail.com'       // Altere aqui
        ];
    }
}
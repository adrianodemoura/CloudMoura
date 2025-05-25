<?php

namespace CloudMoura\Includes;

use CloudMoura\Config\SmsDev as SmsConfig;
use CloudMoura\Includes\Logs;

class SmsDev {
    private string $key;
    private Logs $Logs;

    public function __construct()
    {
        $this->key = SmsConfig::getConfig()['smsdev_key'];
        $this->Logs = new Logs();
    }

    public function send($to, $message) : bool | \Exception {
        $url = "https://api.smsdev.com.br/v1/send";

        $payload = [
            "key" => $this->key,
            "type" => 9, // comum (pode usar 9 ou 5)
            "number" => $to,
            "msg" => $message
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => http_build_query($payload)
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error || $httpCode >= 400) {
            throw new \Exception( "Erro ao enviar SMS (SMSDev): " . ($error ?: $response) );
        }

        $this->Logs->debug("SMS enviado para {$to}: {$message}", 'sms');
        return true;
    }
}

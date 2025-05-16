<?php

namespace CloudMoura\Api\Controllers;

use CloudMoura\Includes\Debug;

class Painel {
    private Debug $debug;
    private string $lastError = "";

    public function __construct() {
        $this->debug = new Debug();
    }

    public function getLastError() : string {
        return $this->lastError;
    }

    public function index() : array {
        $data = [
            "message" => "mas oe sílvio"
        ];

        return $data;
    }
}
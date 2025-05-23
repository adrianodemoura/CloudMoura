<?php

namespace CloudMoura\Api\Controllers;

use CloudMoura\Includes\Logs;

class Controller {
    protected Logs $Logs;
    protected string $lastError = "";
    protected array $postData = [];

    public function __construct() {
        $this->Logs = new Logs();
    }

    public function index() : array {
        return [ 'message' => 'Você esqueceu de configurar a rota! :( :(' ];
    }

    public function getLastError() : string {
        return $this->lastError;
    }

    public function setPostData(array $postData) : void {
        $this->postData = $postData;
    }
}
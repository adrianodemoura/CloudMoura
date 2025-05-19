<?php

// Função para sanitizar entrada
function sanitizeInput($value) {
    // Retorna imediatamente se for null ou não for string
    if ($value === null || !is_string($value)) {
        return $value;
    }
    
    // Remove espaços extras e sanitiza
    $value = trim($value);
    return !empty($value) ? htmlspecialchars($value || '', ENT_QUOTES, 'UTF-8') : $value;
}


// Função de debug melhorada
function debug($data, $die = false) {
    echo "<pre style='font-size: 12px; background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #dee2e6;'>";
    if (is_array($data) || is_object($data)) {
        print_r($data);
    } else {
        var_dump($data);
    }
    echo "</pre>";
    if ($die) die();
}

// Função para validar CSRF
function validateCSRF() {
    if (!isset($_POST[CSRF_TOKEN_NAME]) || $_POST[CSRF_TOKEN_NAME] !== $_SESSION[CSRF_TOKEN_NAME]) {
        header('HTTP/1.1 403 Forbidden');
        die('CSRF token validation failed');
    }
}

// Função para tratamento de erros
function handleError($errno, $errstr, $errfile, $errline) {
    $Logs = new \CloudMoura\Includes\Logs();
    $Logs->write('PHP Error: ' . $errstr, 'error');
    
    if (ini_get('display_errors')) {
        echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px;'>";
        echo "<strong>Erro:</strong> $errstr<br>";
        echo "<strong>Arquivo:</strong> $errfile<br>";
        echo "<strong>Linha:</strong> $errline";
        echo "</div>";
    }
    
    return true;
}
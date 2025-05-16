<?php
// Configurações básicas
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('America/Sao_Paulo');
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

// Configurações de segurança
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Configuração de cache
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// Define o diretório raiz
define('DIR_ROOT', dirname(__DIR__) );

// Inclui arquivos necessários
require_once DIR_ROOT . "/includes/Functions.php";
require_once DIR_ROOT . "/config/Definitions.php";
require_once DIR_ROOT . "/config/Autoload.php";

// Gera token CSRF se não existir
if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
    $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
}

// Verifica se é uma requisição para a API
if (strpos($uri, '/api/') !== false) {
    require_once DIR_ROOT . "/api/index.php";
    exit;
}

// Verifica se o usuário está logado
require_once DIR_ROOT . "/includes/CheckSession.php";

// Registra o handler de erros
set_error_handler('handleError');
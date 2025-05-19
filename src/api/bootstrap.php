<?php
use CloudMoura\Includes\Debug;

$Debug = new Debug();

// Validação de entrada
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $input && json_last_error() !== JSON_ERROR_NONE) {
    $Debug->write('JSON inválido recebido: ' . json_last_error_msg() . ' - Input: ' . $input, 'error');
    Response::error('JSON inválido', 400);
}

// Validação de CSRF para requisições não-GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    if (!$token || $token !== $_SESSION[CSRF_TOKEN_NAME]) {
        // $Debug->write('Token CSRF inválido - Token: ' . $token . ' - Esperado: ' . $_SESSION[CSRF_TOKEN_NAME], 'error');
        // Response::error('Token CSRF inválido', 403);
    }
}

// Rate limiting usando sessão
$ip = $_SERVER['REMOTE_ADDR'];
$rateLimitKey = "rate_limit_{$ip}";

if (!isset($_SESSION[$rateLimitKey])) {
    $_SESSION[$rateLimitKey] = [ 'count' => 0, 'reset' => time() + 60 ];
}

if ($_SESSION[$rateLimitKey]['reset'] < time()) {
    $_SESSION[$rateLimitKey] = [ 'count' => 0, 'reset' => time() + 60 ];
}

$_SESSION[$rateLimitKey]['count']++;

if ($_SESSION[$rateLimitKey]['count'] > 100) {
    $Debug->write('Rate limit excedido - IP: ' . $ip, 'warning');
    Response::error('Muitas requisições. Tente novamente em 1 minuto.', 429);
}

// Log da requisição
// $Debug->write('Requisição recebida - Método: ' . $_SERVER['REQUEST_METHOD'] . ' - URI: ' . $_SERVER['REQUEST_URI'] . ' - IP: ' . $ip . ' - Usuário: ' . ($_SESSION['user']['email'] ?? 'não autenticado'), 'info');

// Verifica o método da requisição
if ($_SERVER['REQUEST_METHOD'] !== 'POST' 
    && $_SERVER['REQUEST_METHOD'] !== 'GET' 
    && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    $Debug->write('Método não permitido: ' . $_SERVER['REQUEST_METHOD'], 'error');
    Response::error('Método não permitido', 405);
}

// Pega a URI e divide em partes
$arrUri = explode( "/", trim($_SERVER['REQUEST_URI'], '/') );
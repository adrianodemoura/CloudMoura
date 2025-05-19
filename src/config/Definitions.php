<?php
// Processa a URI
$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
$arrUri = explode("/", $uri);
$uriContent = !empty($arrUri[1]) ? $arrUri[1] : "login";
$pageError = "";
$arrSqls = [];

// Sanitiza entradas
$_GET = sanitizeInput($_GET);
$_POST = sanitizeInput($_POST);
$_REQUEST = sanitizeInput($_REQUEST);

// Configurações
if ( !file_exists( DIR_ROOT . '/config.json' ) ) {
    file_put_contents( DIR_ROOT . '/config.json', json_encode( ['block'=>false, 'debug'=>true], JSON_PRETTY_PRINT ) );
    chmod( DIR_ROOT . '/config.json', 0775 );
}
$config = json_decode( file_get_contents( DIR_ROOT . '/config.json' ), true );

define('DEBUG', $config['debug'] );
define('BLOCK', $config['block'] );
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PUBLIC_URLS', ['/', '/login', '/sair', '/page_error', '/contato', '/cadastrar'] );
define('PUBLIC_URLS_BLOCK', ['/site_manutencao', '/desbloquear'] );

// Configurações gerais
define('APP_NAME', 'CloudMoura');
define('APP_DESCRIPTION', 'Seu Armazanamento em Nuvem');

// Configurações de upload
define('MAX_FILE_SIZE', 5 * 1024 * 1024 * 1024); // 5GB em bytes
define('ALLOWED_EXTENSIONS', ['mp4', 'srt'] ); // Apenas arquivos MP4 e SRT

// Diretórios
define('DIR_UPLOAD', DIR_ROOT . '/uploads');
define('DIR_DATA', DIR_ROOT . '/data');
define('DIR_LOG', DIR_ROOT . '/logs');
define('DIR_API', DIR_ROOT . '/api');

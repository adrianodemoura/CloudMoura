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
    file_put_contents( DIR_ROOT . '/config.json', json_encode( 
        [
            'block'=>false, 
            'debug'=>true
        ], 
        JSON_PRETTY_PRINT ) );
    chmod( DIR_ROOT . '/config.json', 0775 );
}
$config = json_decode( file_get_contents( DIR_ROOT . '/config.json' ), true );

// debug
define('DEBUG', $config['debug'] );
define('BLOCK', $config['block'] );
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PUBLIC_URLS', ['/', '/login', '/sair', '/page_error', '/contato', '/cadastrar', '/ativar'] );
define('PUBLIC_URLS_BLOCK', ['/site_manutencao', '/desbloquear'] );

// Perfis
define( 'ROLES', ['admin', 'user', 'guest' ] );

// Diretórios
define('DIR_API', DIR_ROOT . '/Api');
define('DIR_UPLOAD', DIR_ROOT . '/storage/uploads');
define('DIR_DATA', DIR_ROOT . '/storage/data');
define('DIR_LOG', DIR_ROOT . '/storage/logs');

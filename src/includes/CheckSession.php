<?php
// Cria diretórios se não existirem
if ( !file_exists(DIR_LOG) ) {
    mkdir(DIR_LOG, 0777, true);
    chmod(DIR_LOG, 0777);
}
if ( !file_exists(DIR_UPLOAD) ) {
    mkdir(DIR_UPLOAD, 0777, true);
    chmod(DIR_UPLOAD, 0777);
}
if ( !file_exists(DIR_DATA) ) {
    mkdir(DIR_DATA, 0775, true);
    chmod(DIR_DATA, 0775);

    // Lê o arquivo Database.php para pegar o nome do banco de dados
    $databaseFile = file_get_contents(DIR_ROOT . '/config/Database.php');
    if (preg_match("/const\s+DB_PATH\s*=\s*DIR_DATA\s*\.\s*'([^']+)'/", $databaseFile, $matches)) {
        $dbPath = $matches[1];
        touch(DIR_DATA . $dbPath);
        chmod(DIR_DATA . $dbPath, 0775);

        // Carrega o autoloader
        require_once DIR_ROOT . '/config/Autoload.php';
        
        // Instancia o Db e cria as tabelas
        $db = new \CloudMoura\Includes\Db();
        $db->createTables();
    }
}

// Verifica a sessão de usuários
if ( isset($_SESSION['user']) ) {
    if ( !file_exists(DIR_UPLOAD . "/{$_SESSION['user']['id']}") ) {
        mkdir(DIR_UPLOAD . "/{$_SESSION['user']['id']}", 0775, true);
        chmod(DIR_UPLOAD . "/{$_SESSION['user']['id']}", 0775);
    }

    if ( !file_exists(DIR_UPLOAD . "/{$_SESSION['user']['id']}/filmes") ) {
        mkdir(DIR_UPLOAD . "/{$_SESSION['user']['id']}/filmes", 0775, true);
        chmod(DIR_UPLOAD . "/{$_SESSION['user']['id']}/filmes", 0775);
    }

    if ( !file_exists(DIR_UPLOAD . "/{$_SESSION['user']['id']}/series") ) {
        mkdir(DIR_UPLOAD . "/{$_SESSION['user']['id']}/series", 0775, true);
        chmod(DIR_UPLOAD . "/{$_SESSION['user']['id']}/series", 0775);
    }
}
// Verifica se o site está bloqueado
if ( BLOCK ) {
    if ( !in_array($uri, PUBLIC_URLS_BLOCK) && !empty($_SESSION['user']['role']) === 'admin' ) {
        header('Location: /site_manutencao');
        exit;
    }
} else {
    // Verifica autenticação e redireciona se necessário
    if ( isset($_SESSION['user']) && $uriContent !== 'admin' ) {
        header('Location: /admin');
        exit;
    }

    if ( !isset($_SESSION['user']) && !in_array('/' . $uriContent, PUBLIC_URLS)) {
        header('Location: /login');
        exit;
    }

    // Processa logout
    if ($uri === "/sair" || $uri === "/admin/sair") {
        session_destroy();
        header('Location: /admin');
        exit;
    }

    // Se está logado e acessa a página de login, redireciona para o admin
    if ($uri === '/login' && isset($_SESSION['user'])) {
        header('Location: /admin');
        exit;
    }
}
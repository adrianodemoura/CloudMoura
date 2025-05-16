<?php

// Cria diretórios se não existirem
if ( !file_exists(DIR_LOG) ) {
    mkdir(DIR_LOG, 0775, true);
    chmod(DIR_LOG, 0775);
    chown(DIR_LOG, 'www-data');
    chgrp(DIR_LOG, 'www-data');
}
if ( !file_exists(DIR_UPLOAD) ) {
    mkdir(DIR_UPLOAD, 0775, true);
    chmod(DIR_UPLOAD, 0775);
    chown(DIR_UPLOAD, 'www-data');
    chgrp(DIR_UPLOAD, 'www-data');
}

// Verifica se o site está bloqueado
if ( BLOCK ) {
    if ( !in_array($uri, PUBLIC_URLS_BLOCK) ) {
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
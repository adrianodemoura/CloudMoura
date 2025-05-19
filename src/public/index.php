<?php
    require_once dirname(__DIR__) . "/public/bootstrap.php";

    $classContainer = "bg-white";
    if ( in_array( $uri, array_merge(PUBLIC_URLS, PUBLIC_URLS_BLOCK) ) ) {
        $classContainer = "flex-grow-1 d-flex flex-column h-100 justify-content-center align-items-center";
    }

    // Verifica se a página existe
    if (!file_exists(DIR_ROOT . "/public/pages/{$uriContent}/index.php")) {
        $pageError = $uriContent;
        $uriContent = "page_error";
    }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= APP_DESCRIPTION ?>">
    <meta name="author" content="CloudMoura">
    <meta name="theme-color" content="#0d6efd">
    
    <!-- Meta tags de segurança -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:;">
    
    <title><?= APP_NAME . ' - ' . APP_DESCRIPTION ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/img/favicon/icon.svg">
    <link rel="manifest" href="/img/favicon/site.webmanifest">
    
    <!-- Preload -->
    <link rel="preload" href="/css/font_awesome_free.all.min.css" as="style">
    <link rel="preload" href="/css/bootstrap.min.css" as="style">
    <link rel="preload" href="/js/bootstrap.bundle.min.js" as="script">

    <!-- CSS -->
    <link rel="stylesheet" href="/css/font_awesome_free.all.min.css">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/general.css">
    
    <!-- JavaScript -->
    <script src="/js/inputmask.min.js"></script>
    <script src="/js/bootstrap.bundle.min.js" defer></script>
    <script src="/js/general.js" defer></script>
</head>

<body class="h-100">
    <div id="corpo" class="container min-vh-100 d-flex flex-column">
        <div id="content" class="flex-grow-1 d-flex flex-column h-100">

            <!-- Container Principal -->
            <div id="divContentContainer" class="<?= $classContainer; ?>">
                <?php require_once DIR_ROOT . "/public/pages/{$uriContent}/index.php"; ?>
                <!-- uma nova percepção -->
            </div>

            <!-- Toast de Alerta -->
            <div id="divAlertToast" class="toast-container position-fixed top-0 start-50 w-50 translate-middle-x p-3 z-3 z-index-1">
                <div id="alertToast" class="toast text-white w-100" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header text-white fs-4">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        <div class="w-100 text-center">
                            <strong>Atenção</strong>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Fechar"></button>
                    </div>
                    <div class="toast-body fs-6" id="alertMessage"></div>
                </div>
            </div>
        </div>

        <footer id="divFooter" class="mt-4 pt-3 border-top bg-light">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start">
                        <p class="text-muted mb-0">&copy; <?= date('Y') ?> CloudMoura. Todos os direitos reservados.</p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <p class="text-muted mb-0">
                            &nbsp;
                        </p>
                    </div>
                </div>
            </div>
        </footer>

        <div id="divDebug" class="mt-auto">
            <?php if ( DEBUG ) require_once dirname(__DIR__) . "/public/pages/debug/index.php"; ?>
        </div>
    </div>

    <!-- CSRF Token -->
    <input type="hidden" id="csrfTokenName" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
</body>
</html>
<?php
$executionTime = round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2);
echo "<!-- Tempo de carregamento: {$executionTime}ms -->";

if ( !isset($_SESSION['user']) ) : ?>
<script>
    sessionStorage.removeItem('user');
</script>
<?php endif; ?>

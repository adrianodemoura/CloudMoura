<?php
use CloudMoura\Includes\Logs;
use CloudMoura\Includes\Response;

$Logs = new Logs();
include DIR_API . "/bootstrap.php";

try {
    // Verifica se temos pelo menos 2 partes na URI (api/controller)
    if (count($arrUri) < 2) {
        throw new Exception('URI inválida');
    }

    // Verifica se o site está bloqueado
    if ( BLOCK && $uri !== "/api/user/desbloquear" ) {
        throw new \Exception( "O site está em manutenção, tente novamente mais tarde!", 401 );
    }

    // Pega o nome do controller e da action
    $controllerName = ucfirst($arrUri[1] ?? 'Painel');
    $actionName = $arrUri[2] ?? 'index';

    // Monta o namespace completo do controller
    $controllerClass = "\\CloudMoura\\Api\\Controllers\\{$controllerName}Controller";
    
    // Verifica se o arquivo do controller existe
    $controllerFile = DIR_API . "/Controllers/{$controllerName}Controller.php";
    if (!file_exists($controllerFile)) {
        throw new Exception("Controller não encontrado: {$controllerName}");
    }

    // Inclui o arquivo do controller
    require_once $controllerFile;

    // Verifica se a classe existe
    if (!class_exists($controllerClass)) {
        throw new Exception("Classe do controller não encontrada: {$controllerClass}");
    }

    // Instancia o controller
    $controller = new $controllerClass();
    $controller->setPostData( $data );

    // Verifica se o método existe
    if (!method_exists($controller, $actionName)) {
        throw new Exception("Action não encontrada: {$actionName}");
    }

    // Chama a action e pega o resultado
    $result = $controller->$actionName();

    // Retorna o resultado
    Response::success($result);

} catch (Exception $e) {
    $Logs->debug( $e->getCode() . " - " . $e->getMessage(), 'error' );
    Response::error($e->getMessage(), $e->getCode() ?? 500);
}

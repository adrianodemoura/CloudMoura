<?php

use CloudMoura\Api\Includes\Response;
use CloudMoura\Api\Middleware\Auth;
use CloudMoura\Includes\Db;

// Verifica autenticação
Auth::check();

try {
    $input = file_get_contents('php://input');
    $arrInput = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return Response::error('JSON inválido: ' . json_last_error_msg(), 400);
    }

    if (!isset($arrInput['email'])) {
        return Response::error('Email é obrigatório para excluir o cadastro!', 400);
    }

    $Db = new Db();

    

} catch (\Throwable $th) {
    return Response::error('Erro ao processar exclusão: ' . $th->getMessage(), 500);
}

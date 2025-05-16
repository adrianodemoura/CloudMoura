<?php

use CloudMoura\Api\Includes\Response;
use CloudMoura\Includes\Db;

try {
    $input = file_get_contents('php://input');
    $arrInput = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return Response::error('JSON inválido: ' . json_last_error_msg(), 400);
    }


    

    $Db = new Db();

    

} catch (\Throwable $th) {
    // Verifica se é um erro de violação de unicidade (email já cadastrado)
    if (strpos($th->getMessage(), 'UNIQUE constraint failed') !== false) {
        return Response::error('Este e-mail já está cadastrado.', 400);
    }
    
    return Response::error('Erro ao processar cadastro: ' . $th->getMessage(), 500);
}

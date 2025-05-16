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
        return Response::error('Email é obrigatório para ativar/desativar o cadastro!', 400);
    }

    $Db = new Db();

    $res = $Db->query("SELECT * FROM users WHERE email = :email", ['email' => $arrInput['email']]);
    if (empty($res)) {
        return Response::error('O e-mail "' . $arrInput['email'] . '" NÃO está cadastrado!', 400);
    }

    $Db->query("UPDATE users SET active = :active WHERE email = :email", [
        'active' => $arrInput['active'] ? 1 : 0,
        'email' => $arrInput['email']
    ]);

    if (!empty($Db->getLastError())) {
        return Response::error('Erro ao atualizar o status do cadastro! ' . $Db->getLastError(), 500);
    }

    return Response::success([
        'user' => ['email' => $arrInput['email']]
    ], 'Status do cadastro atualizado com sucesso.');

} catch (\Throwable $th) {
    return Response::error('Erro ao processar atualização: ' . $th->getMessage(), 500);
}

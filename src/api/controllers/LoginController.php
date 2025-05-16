<?php

namespace CloudMoura\Api\Controllers;

use CloudMoura\Api\Controllers\Controller;
use CloudMoura\Includes\Db;

class LoginController extends Controller {
    private Db $Db;

    public function __construct() {
        parent::__construct();
        $this->Db = new Db();
    }

    public function login() : array | \Exception {
        $res = $this->Db->query("SELECT * FROM users WHERE email = :email AND password = :password", [
            'email' =>  $this->postData['email'],
            'password' => $this->postData['password']
        ]);

        if ( empty( $res ) ) {
            throw new \Exception( 'Usuário ou senha inválidos!', 401 );
        }

        if ( $res[0]['active'] === 0 ) {
            throw new \Exception( 'Usuário desativado!', 401 );
        }

        // Atualiza o último login do usuário
        $this->Db->query("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id", [ 'id' => $res[0]['id'] ]);

        $_SESSION['user'] = [
            'email' => $res[0]['email'],
            'role' => $res[0]['role'],
            'id' => $res[0]['id']
        ];

        return [ 'message' => 'Login realizado com sucesso.', 'email' => $res[0]['email'] ];
    }

    public function logout() : array | \Exception {
        unset($_SESSION['user']);

        // Atualiza o último login do usuário
        $this->Db->query("UPDATE users SET last_login = NULL WHERE id = :id", [ 'id' => $res[0]['id'] ]);

        return [ 'message' => 'Logout realizado com sucesso.' ];
    }
}
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
        try {
            // Tenta o login
            $res = $this->Db->query("SELECT * FROM users WHERE email = :email", [
                'email' =>  $this->postData['email']
            ] );

            // Verifica se o usuário existe
            if ( empty( $res ) ) {
                $this->debug->write("Login falhou para o usuário: " . $this->postData['email'], "login");
                throw new \Exception( 'Usuário ou senha inválidos!', 401 );
            }

            // Verifica a senha
            if (!password_verify($this->postData['password'], $res[0]['password'])) {
                $this->debug->write("Senha inválida para o usuário: " . $this->postData['email'], "login");
                throw new \Exception( 'Usuário ou senha inválidos!', 401 );
            }

            // Verifica se o usuário está ativo
            if ( $res[0]['active'] === 0 ) {
                $this->debug->write("Usuário desativado: " . $this->postData['email'], "login");
                throw new \Exception( 'Usuário desativado!', 401 );
            }
            $lastLogin = $res[0]['last_login'];

            // Atualiza o último login do usuário
            $this->Db->query("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id", [ 'id' => $res[0]['id'] ]);

            // Cria a sessão do usuário
            $_SESSION['user'] = [
                'name' => $res[0]['name'],
                'email' => $res[0]['email'],
                'role' => $res[0]['role'],
                'last_login' => $lastLogin,
                'id' => $res[0]['id']
            ];
            
            // retorna o usuário logado
            $this->debug->write("Login realizado com sucesso para: " . $res[0]['email'], "login");
            return [ 'message' => 'Login realizado com sucesso.', 'email' => $res[0]['email'] ];
        } catch (\Exception $e) {
            $this->debug->write("Erro no login: " . $e->getMessage(), "error");
            throw $e;
        }
    }

    public function logout() : array | \Exception {
        unset($_SESSION['user']);

        // Atualiza o último login do usuário
        $this->Db->query("UPDATE users SET last_login = NULL WHERE id = :id", [ 'id' => $res[0]['id'] ]);

        return [ 'message' => 'Logout realizado com sucesso.' ];
    }
}
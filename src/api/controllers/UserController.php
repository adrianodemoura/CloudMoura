<?php

namespace CloudMoura\Api\Controllers;

use CloudMoura\Api\Controllers\Controller;
use CloudMoura\Includes\Db;

class UserController extends Controller {
    private Db $Db;

    public function __construct() {
        parent::__construct();
        $this->Db = new Db();
    }

    public function create() : array | \Exception {
        
        if (!isset($this->postData['email']) || !isset($this->postData['password'])) {
            throw new \Exception( 'Email e senha são obrigatórios para um novo cadastro.', 400 );
        }

        if (!empty( $this->postData['phone'])) { // Remove máscara do telefone se existir
            $this->postData['phone'] = preg_replace( '/[^0-9]/', '', $this->postData['phone'] );
        }

        $res = $this->Db->query("SELECT * FROM users WHERE email = :email", [ 'email' => $this->postData[ 'email' ] ] );
        if ( ! empty( $res ) ) {
            throw new \Exception( 'O e-mail "' . $this->postData[ 'email' ] . '" já foi cadastrado!', 400);
        }

        $this->Db->query("INSERT INTO users (name, email, phone, password) VALUES (:name, :email, :phone, :password)", [
            'name' => $this->postData[ 'name' ],
            'email' => $this->postData[ 'email' ],
            'phone' => $this->postData[ 'phone' ],
            'password' => $this->postData[ 'password' ]
        ]);

        if (!empty($this->Db->getLastError())) {
            throw new \Exception( 'Erro ao tentar INSERIR cadastro!', 500);
        }

        return [
            'message' => 'Cadastro executado com sucesso.',
            'user' => [ 'email' => $this->postData[ 'email' ] ]
        ];
    }

    public function update() : array | \Exception {
        
        if (!isset($this->postData['email'])) {
            throw new \Exception( 'Email é obrigatório para atualizar o cadastro!', 400 );
        }
    
        if (!empty($this->postData['phone'])) {
            $this->postData['phone'] = preg_replace('/[^0-9]/', '', $this->postData['phone']);
        }

        $res = $this->Db->query("SELECT * FROM users WHERE email = :email", ['email' => $this->postData['email']]);
        if (empty($res)) {
            throw new \Exception( 'O e-mail "' . $this->postData['email'] . '" NÃO está cadastrado!', 400 );
        }

        if ( $res[0]['active'] === 0 ) {
            throw new \Exception( 'Usuário desativado.', 401 );
        }

        $arrUpdate = [];
        $arrFields = [];
        if (!empty($this->postData['name'])) {
            $arrFields[] = 'name = :name';
            $arrUpdate['name'] = $this->postData['name'];
        }
        if (!empty($this->postData['phone'])) {
            $arrFields[] = 'phone = :phone';
            $arrUpdate['phone'] = $this->postData['phone'];
        }
        if (!empty($this->postData['password'])) {
            $arrFields[] = 'password = :password';
            $arrUpdate['password'] = $this->postData['password'];
        }

        $this->Db->query("UPDATE users SET " . implode(', ', $arrFields) . " WHERE email = :email", array_merge($arrUpdate, ['email' => $this->postData['email']]));

        if (!empty($this->Db->getLastError())) {
            $this->Debug->write( 'Erro ao atualizar o cadastro! ' . $this->Db->getLastError(), 'error' );
            throw new \Exception( 'Erro ao atualizar o cadastro! ', 500 );
        }

        return [
            'message' => 'Cadastro atualizado com sucesso.',
            'user' => ['email' => $this->postData['email']]
        ];
    }

    public function delete() : array | \Exception {

        if (!isset($this->postData['email'])) {
            throw new \Exception( 'Email é obrigatório para excluir o cadastro!', 400 );
        }

        $res = $this->Db->query("SELECT id, name, email, active FROM users WHERE email = :email", [ 'email' => $this->postData['email'] ]);
        if ( empty( $res ) ) {
            throw new \Exception( 'O e-mail "' . $this->postData['email'] . '" NÃO está cadastrado!', 400 );
        }

        if ( $res[0]['id'] === 1 ) {
            throw new \Exception( "o Usuário " . $res[0]['email'] . " não pode ser excluído.", 401 );
        }

        $this->Db->query("DELETE FROM users WHERE email = :email", [ 'email' => $this->postData['email'] ]);
        if ( !empty( $this->Db->getLastError() ) ) {
            throw new \Exception( 'Erro ao tentar EXCLUIR cadastro!', 500 );
        }

        return [ 'message' => 'Usuário "' . $this->postData['email'] . '" excluído com sucesso.', 'email' => $this->postData['email'] ];
    }

    public function get() : array | \Exception {
        $res = $this->Db->query("SELECT * FROM users WHERE email = :email", [ 'email' => $this->postData['email'] ]);
        if ( empty( $res ) ) {
            throw new \Exception( 'Nenhum usuário cadastrado!', 400 );
        }
        unset( $res[0]['password'] );

        return [ 'user' => $res[0], 'message' => 'Usuário encontrado com sucesso.' ];
    }

    public function getList() : array | \Exception {
        try {
            $page = $this->postData['page'] ?? 1;
            $limit = $this->postData['limit'] ?? 10;
            $offset = ($page - 1) * $limit;

            $res = $this->Db->query("SELECT id, name, email, phone, active, role, last_login, created_at 
                FROM users ORDER BY name DESC LIMIT :limit OFFSET :offset", [ 'limit' => $limit, 'offset' => $offset ] );
            
            $total = $this->Db->query("SELECT COUNT(*) as total FROM users")[0]['total'];
            $lastPage = ceil($total / $limit);

            return [
                'success' => true,
                'total' => $total,
                'current_page' => (int)$page,
                'last_page' => $lastPage,
                'users' => $res,
                'message' => 'Usuários encontrados com sucesso.'
            ];
        } catch (\Exception $e) {
            throw new \Exception('Erro ao listar usuários: ' . $e->getMessage(), 500);
        }
    }

    public function activate() : array | \Exception {

        $res = $this->Db->query("SELECT id, name, email, active FROM users WHERE email = :email", [ 'email' => $this->postData['email'] ]);
        if ( empty( $res ) ) {
            throw new \Exception( 'Nenhum usuário cadastrado!', 400 );
        }

        if ( $res[0]['id'] === 1 ) {
            throw new \Exception( "o Usuário " . $res[0]['email'] . " não pode ser desativado!", 401 );
        }

        $newStatus = $res[0]['active'] == 1 ? 0 : 1;

        $this->Db->query("UPDATE users SET active = :active WHERE email = :email", [ 'active' => $newStatus, 'email' => $this->postData['email'] ]);
        if ( empty( $res ) ) {
            throw new \Exception( 'Nenhum usuário cadastrado!', 400 );
        }

        return [ 'message' => 'Usuário ' . ($newStatus == 1 ? 'ativado' : 'desativado') . ' com sucesso.' ];
    }

    public function desbloquear() : array | \Exception {
        $res = $this->Db->query("SELECT id, email FROM users WHERE role='admin' AND password = :password", [ 'password' => $this->postData['password'] ]);
        if ( empty( $res ) ) {
            throw new \Exception( 'Usuário inválido para DESBLOQUEAR o site!', 400 );
        }

        $config = json_decode( file_get_contents( DIR_ROOT . '/config.json' ), true );
        $config['block'] = false;
        file_put_contents( DIR_ROOT . '/config.json', json_encode( $config, JSON_PRETTY_PRINT ) );

        return [ 'message' => 'Site DESBLOQUEADO com sucesso.' ];
    }
}

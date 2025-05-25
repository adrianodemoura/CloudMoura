<?php
namespace CloudMoura\Api\Controllers;

use CloudMoura\Api\Controllers\Controller;
use CloudMoura\Includes\Email;
use CloudMoura\Includes\SmsDev as Sms;
use CloudMoura\Includes\Db;

class UserController extends Controller {
    private Db $Db;
    private Email $Email;
    private Sms $Sms;

    public function __construct() { 
        parent::__construct();
        $this->Db = new Db();
        $this->Email = new Email();
        $this->Sms = new Sms();
    }

    public function create() : array | \Exception {
        $codeActivation = $this->postData[ 'code_activation' ] ?? null;

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
        $this->postData[ 'active' ] = 1;
        if ( !empty( $codeActivation ) ) {
            $this->postData[ 'active' ] = 0;
        }

        // Inserindo o usuário
        $this->Db->query("INSERT INTO users (name, email, phone, password, code_activation, active) 
            VALUES (:name, :email, :phone, :password, :code_activation, :active)", [
            'name' => $this->postData[ 'name' ],
            'email' => $this->postData[ 'email' ],
            'phone' => $this->postData[ 'phone' ],
            'active' => $this->postData[ 'active' ],
            'password' => password_hash($this->postData['password'], PASSWORD_DEFAULT)
        ]);

        if ( !empty($this->Db->getLastError()) ) {
            throw new \Exception( 'Erro ao tentar INSERIR cadastro! ' . $this->Db->getLastError(), 500);
        }

        // atualizando o código de ativação
        if ( !empty( $codeActivation ) ) {
            $res = $this->Db->query("SELECT * FROM users WHERE email = :email", [ 'email' => $this->postData[ 'email' ] ]);
            if ( empty( $res ) ) {
                throw new \Exception( 'O e-mail "' . $this->postData[ 'email' ] . '" não foi encontrado!', 400 );
            }

            // Atualizando o código de ativação
            $codeActivation = $res[0]['id'] . "." . $this->postData['code_activation'];
            $this->Db->query("UPDATE users SET code_activation = :code_activation WHERE email = :email", [
                'code_activation' => $codeActivation,
                'email' => $this->postData[ 'email' ]
            ] );
        }

        // Mensagem de boa vindas e ativação
        $messageReturn = "Cadastro executado com sucesso.";
        $messageBody = "";
        $messageBody .= "<img src='{$_ENV["HTTP_HOST"]}/img/logo.png' alt='Logo CloudMoura' style='width: 300px;'/>";
        $messageBody .= "<h1>Olá {$this->postData['name']},</h1>";
        $messageBody .= "<p>Seu cadastro foi realizado com sucesso!</p>";
        $messageBody .= "<p>Agora você já pode acessar o sistema.</p>";
        $messageBody .= "<p>Clique <a href='{$_ENV["HTTP_HOST"]}/ativar/{$codeActivation}'>AQUI</a> para ativar sua conta.</p>";
        $messageBody .= "<p>Seu código de ativação é: <strong>$codeActivation</strong></p>";
        $messageBody .= "<p>Atenciosamente,</p>";
        $messageBody .= "<p>Equipe {$_ENV["APP_NAME"]}</p>";
        $messageBody .= "<br /><br />";
        $messageBody .= "<p><small>Este é um e-mail automático, não responda.</small></p>";
        $messageBody .= "<p><small>Se você não se cadastrou, ignore este e-mail.</small></p>";

        // não impede a criação do usuário.
        if ( isset( $_ENV[ 'MAIL_USER' ] ) ) {
            $this->Email->send( $this->postData[ 'email' ], "Cadastro na {$_ENV["APP_NAME"]}", $messageBody );
            $messageReturn .= " O Código de ativação foi enviado para o seu e-mail.";
        }

        // envia SMS, caso tenha chave pra tal ...
        if ( isset( $_ENV[ 'SMSDEV_API_KEY' ] ) ) {
            $this->Sms->send( (string) $res[0]['phone'], "Código de Ativação {$codeActivation} 145541" );
            $messageReturn .= " O Código de ativação foi enviado para seu celular ({$res[0]['phone']}).";
        }

        return [
            'message' => $messageReturn,
            'user' => [ 'email' => $this->postData[ 'email' ] ]
        ];
    }

    public function update() : array | \Exception {
        $codeActivation = $this->postData['active_code'] ?? null;
        if ( !empty( $codeActivation ) ) {
            $this->postData['active_code'] = preg_replace('/[^0-9]/', '', $codeActivation);
        }
        
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

        if ( $res[0]['active'] === 0 && empty( $codeActivation ) ) {
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
            $arrUpdate['password'] = password_hash($this->postData['password'], PASSWORD_DEFAULT);
        }
        if (!empty( $codeActivation )) {
            $arrFields[] = 'code_activation = :code_activation';
            $arrUpdate['code_activation'] = $codeActivation;
        }

        $this->Db->query("UPDATE users SET " . implode(', ', $arrFields) . " WHERE email = :email", array_merge($arrUpdate, ['email' => $this->postData['email']]));

        if (!empty($this->Db->getLastError())) {
            $this->Logs->debug( 'Erro ao atualizar o cadastro! ' . $this->Db->getLastError(), 'error' );
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

    public function activateByCode() : array | \Exception {

        if ( empty( $this->postData['code'] ) ) {
            throw new \Exception( 'Código de ativação é obrigatório!', 400 );
        }
        $id = explode( '.', $this->postData['code'] );
        if ( empty( $id[0] ) || empty( $id[1] ) ) {
            throw new \Exception( 'Código de ativação inválido no POST!', 400 );
        }
        $res = $this->Db->query("SELECT id, name, email, active FROM users WHERE id = :id", [ 'id' => $id[0] ]);
        if ( empty( $res ) ) {
            throw new \Exception( 'ID '. $id[0] .' inválido no banco de dados!', 400 );
        }
        if ( $res[0]['active'] == 1 ) {
            throw new \Exception( 'Usuário já ativado!', 400 );
        }
        if ( $res[0]['id'] === 1 ) {
            throw new \Exception( "o Usuário " . $res[0]['email'] . " não pode ser ativado por código!", 401 );
        }
        $this->Db->query("UPDATE users SET active = 1, code_activation = NULL WHERE id = :id", [ 'id' => $res[0]['id'] ]);
        if ( !empty( $this->Db->getLastError() ) ) {
            throw new \Exception( 'Erro ao tentar ATIVAR cadastro! ' . $this->Db->getLastError(), 500);
        }

        return [ 'message' => 'Usuário ' . $res[0]['email'] . ' ativado com sucesso.' ];
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
        $res = $this->Db->query("SELECT id, email, password FROM users WHERE role='admin'");
        if ( empty( $res ) ) {
            throw new \Exception( 'Usuário inválido para DESBLOQUEAR o site!', 400 );
        }

        if (!password_verify($this->postData['password'], $res[0]['password'])) {
            throw new \Exception( 'Senha inválida para DESBLOQUEAR o site!', 400 );
        }

        $config = json_decode( file_get_contents( DIR_ROOT . '/config.json' ), true );
        $config['block'] = false;
        file_put_contents( DIR_ROOT . '/config.json', json_encode( $config, JSON_PRETTY_PRINT ) );

        return [ 'message' => 'Site DESBLOQUEADO com sucesso.' ];
    }

    public function recoverPassword() : array | \Exception {
        if ( empty( $this->postData['email'] ) ) {
            throw new \Exception( 'E-mail é obrigatório para recuperação de senha!', 400 );
        }

        $res = $this->Db->query("SELECT id, name, email FROM users WHERE email = :email", [ 'email' => $this->postData['email'] ]);
        if ( empty( $res ) ) {
            throw new \Exception( 'E-mail não encontrado!', 400 );
        }

        // Gera o código de recuperação
        $code = $res[0]['id'] . "-" . bin2hex( random_bytes( 4 ) );
        $this->Db->query( "UPDATE users SET 
            code_activation = :code_activation,
            active  = 0 
            WHERE email = :email", 
            [ 
                'code_activation' => $code, 
                'email' => $this->postData['email'] 
            ] );

        // Envia o e-mail de recuperação
        $messageBody = "";
        $messageBody .= "<img src='{$_ENV["HTTP_HOST"]}/img/logo.png' alt='Logo CloudMoura' style='width: 300px;'/>";
        $messageBody .= "<h1>Olá {$res[0]['name']},</h1>";
        $messageBody .= "<p>Você solicitou a recuperação de senha.</p>";
        $messageBody .= "<p>Clique <a href='{$_ENV["HTTP_HOST"]}/trocar_senha/{$code}'>AQUI</a> para redefinir sua senha.</p>";
        $messageBody .= "<p>Código de recuperação:</p>";
        $messageBody .= "<p><span class='code'><strong>{$code}</strong></span></p>";
        $messageBody .= "<p>Atenciosamente,</p>";
        $messageBody .= "<p>Equipe {$_ENV["APP_NAME"]}</p>";
        $messageBody .= "<br /><br />";
        $messageBody .= "<p><small>Este é um e-mail automático, não responda.</small></p>";
        $messageBody .= "<p><small>Se você não se cadastrou, ignore este e-mail.</small></p>";

        // Envia o e-mail
        if ( !empty( $_ENV[ 'MAIL_USER' ] ) ) {
            $this->Email->send( $this->postData[ 'email' ], "Recuperação de Senha - {$_ENV["APP_NAME"]}", $messageBody );
        }

        return [ 'message' => 'E-mail de recuperação enviado com sucesso.' ];
    }

    public function resetPassword() : array | \Exception {
        $this->Logs->debug( json_encode( $this->postData ) , 'debug' );
        if ( empty( $this->postData['code'] ) || empty( $this->postData['password'] ) ) {
            throw new \Exception( 'Código e senha são obrigatórios para recuperação de senha!', 400 );
        }

        $id = explode( '-', $this->postData['code'] );
        if ( empty( $id[0] ) || empty( $id[1] ) ) {
            throw new \Exception( 'Código de recuperação inválido no POST!', 400 );
        }

        if ( strlen( $this->postData['password'] ) < 6 ) {
            throw new \Exception( 'A senha deve ter no mínimo 6 caracteres!', 400 );
        }

        $res = $this->Db->query("SELECT id, name, email FROM users WHERE id = :id AND code_activation = :code_activation", 
        [ 
            'id' => $id[0], 
            'code_activation' => $this->postData['code'] 
        ] );
        if ( empty( $res ) ) {
            throw new \Exception( 'Código de ativação inválido no banco de dados!', 400 );
        }

        // Atualiza a senha
        $this->Db->query("UPDATE users SET 
            password = :password, 
            code_activation = NULL,
            active = 1
            WHERE id = :id", [
                'password' => password_hash( $this->postData['password'], PASSWORD_DEFAULT ),
                'id' => $id[0]
            ]);

        return [ 'message' => 'Senha redefinida com sucesso.' ];
    }
}

<?php

namespace CloudMoura\Includes;

use CloudMoura\Config\Database;
use PDO;
use PDOException;

class Db {
    private PDO $pdo;
    private Debug $debug;
    protected $LastCodeError = null;
    protected $LastError = "";

    public function __construct() {
        $this->debug = new Debug();
        $this->debug->write("Inicializando conexão com o banco de dados", "database");

        try {
            // Tenta conectar ao banco
            $this->pdo = new PDO( Database::getDSN(), null, null, Database::PDO_OPTIONS );
            $this->debug->write("Conexão com o banco de dados estabelecida com sucesso", "database");
        } catch ( PDOException $e ) {
            $this->LastCodeError = $e->getCode();
            $this->LastError = $e->getMessage();
            $this->debug->write("Erro ao conectar ao banco de dados: " . $e->getCode() ." - " . $e->getMessage(), "error");
        }
    }

    /**
     * Executa uma query SQL e retorna o resultado
     * @param string $sql Query SQL a ser executada
     * @param array $params Parâmetros para a query (opcional)
     * @return array Resultado da query
     */
    public function query(string $sql, array $params = []): array {
        $res = [];
        if ( $this->LastCodeError ) {
            return $res;
        }
        try {
            $this->debug->write("Executando query: " . $sql, "database");
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $res = $stmt->fetchAll();
            $this->debug->write("Parâmetros da query: " . json_encode( $params ), "database");
            $this->debug->write("Resultado da query: " . json_encode( $res ), "database");
        } catch (\Throwable $th) {
            $this->LastCodeError = $th->getCode();
            $this->LastError = $th->getMessage();
            $this->debug->write("Erro ao executar query: " . $this->LastCodeError . " - " . $this->LastError, "database");
        }

        return $res;
    }

    /**
     * Cria a tabela de usuários se não existir e insere o usuário admin@admin.com com a senha Mudar123#
     * @return void
     */
    public function createTables() : void {
        try {
            $this->debug->write("Criando tabela de usuários...", "database");
            
            // Cria a tabela de usuários
            $this->query("CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT NOT NULL UNIQUE,
                name TEXT,
                phone TEXT,
                password TEXT NOT NULL,
                active INTEGER DEFAULT 1,
                role TEXT DEFAULT 'user',
                last_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            $this->debug->write("Tabela de usuários criada com sucesso", "database");

            // Verifica se já existe um usuário admin
            $admin = $this->query("SELECT id FROM users WHERE email = 'admin@admin.com'");
            if (empty($admin)) {
                $this->debug->write("Criando usuário admin padrão...", "database");
                // Insere o usuário admin padrão
                $this->query("INSERT INTO users (email, name, password, role) VALUES ('admin@admin.com', 'Administrador', :password, 'admin')", [
                    'password' => password_hash('Admin01', PASSWORD_DEFAULT)
                ]);
                $this->debug->write("Usuário \"admin@admin.com\" com senha \"Admin01\" criado com sucesso", "database");
            } else {
                $this->debug->write("Usuário admin já existe", "database");
            }
        } catch (\Throwable $th) {
            $this->debug->write("Erro ao tentar criar as tabelas do banco de dados: " . $th->getMessage(), "error");
            throw $th;
        }
    }

    /**
     * Retorna o código do último erro
     */
    public function getLastCodeError() : int | string {
        return $this->LastCodeError;
    }

    /**
     * Retorna a última mensagem de erro
     */
    public function getLastError() : string {
        return $this->LastError;
    }
} 
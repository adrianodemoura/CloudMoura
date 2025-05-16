<?php

namespace CloudMoura\Includes;

use CloudMoura\Config\Database;
use PDO;
use PDOException;

class Db {
    private PDO $pdo;
    private Debug $debug;
    protected $LastCodeError = "";
    protected $LastError = "";

    public function __construct() {
        $this->debug = new Debug();
        $this->debug->write("Inicializando conexão com o banco de dados", "database");

        try {
            $this->pdo = new PDO( Database::getDSN(), null, null, Database::PDO_OPTIONS );
            $this->debug->write("Conexão com o banco de dados estabelecida com sucesso", "database");
        } catch (PDOException $e) {
            $this->debug->write("Erro ao conectar ao banco de dados: " . $e->getMessage(), "error");
            // Inicializa com uma conexão vazia para evitar o erro de propriedade não inicializada
            $this->pdo = new PDO('sqlite::memory:');
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

            if ( $this->LastCodeError === 'HY000' ) { $this->setTableUser(); }

            $this->debug->write("Erro ao executar query: " . $this->LastCodeError . " - " . $this->LastError, "database");
        }

        return $res;
    }

    /**
     * Cria a tabela de usuários se não existir e insere o usuário admin@admin.com com a senha Mudar123#
     * @return void
     */
    private function setTableUser(): void {
        try {
            // Cria o diretório data se não existir
            $dbDir = dirname(Database::DB_PATH);
            if (!file_exists($dbDir)) {
                mkdir($dbDir, 0775, true);
                chmod($dbDir, 0775);
                chown($dbDir, 'www-data');
                chgrp($dbDir, 'www-data');
                $this->debug->write("Diretório \"$dbDir\" de dados criado com sucesso", "database");
            }

            // Verifica se o arquivo do banco existe, se não, cria um novo
            if (!file_exists(Database::DB_PATH)) {
                touch(Database::DB_PATH);
                chmod(Database::DB_PATH, 0664);
                chown(Database::DB_PATH, 'www-data');
                chgrp(Database::DB_PATH, 'www-data');
                $this->debug->write("Arquivo do banco de dados criado com sucesso", "database");
            }

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
            $adminExists = $this->query("SELECT COUNT(*) as count FROM users WHERE email = 'admin@admin.com'");
            
            if ($adminExists[0]['count'] == 0) {
                // Insere o usuário admin apenas se não existir
                $this->query("INSERT INTO users (email, name, password, role) VALUES ('admin@admin.com', 'Administrador', 'Admin01', 'admin')");
                $this->debug->write("Usuário admin@admin.com criado com sucesso", "database");
            }
        } catch (\Throwable $th) {
            $this->debug->write("Erro ao configurar banco de dados: " . $th->getMessage(), "error");
            throw $th;
        }
    }

    /**
     * Retorna o código do último erro e a mensagem
     */
    public function getLastError():string {
        $res = "";
        if ( !empty( $this->LastCodeError ) ) { 
            $res .= $this->LastCodeError . " - ";
        }
        if ( !empty( $this->LastError ) ) {
            $res .= $this->LastError;
        }
        return $res;
    }
} 
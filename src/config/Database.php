<?php

namespace CloudMoura\Config;

class Database {
    // Caminho para o arquivo do banco de dados SQLite
    const DB_PATH = DIR_DATA . '/cloudmoura.sqlite';
    
    // Configurações do PDO
    const PDO_OPTIONS = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ];

    // DSN para conexão com SQLite
    public static function getDSN(): string {
        return 'sqlite:' . self::DB_PATH;
    }

    public static function getDBPath(): string {
        return self::DB_PATH;
    }
} 
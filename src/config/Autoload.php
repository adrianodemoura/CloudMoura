<?php

spl_autoload_register(function ($class) {
    try {
        // Converte namespace para caminho do arquivo
        $path = str_replace(['CloudMoura\\', '\\'], ['', '/'], $class);
        $parts = explode('/', $path);
        
        // Converte apenas os diretórios para minúsculo, mantendo o arquivo em camelCase
        $lastPart = array_pop($parts); // Remove o último elemento (nome do arquivo)
        $parts = array_map('strtolower', $parts); // Converte diretórios para minúsculo
        $parts[] = $lastPart; // Adiciona o nome do arquivo de volta
        
        $file = dirname(__DIR__) . '/' . implode('/', $parts) . '.php';
        
        // Se o arquivo existir, carrega-o
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
        
        throw new Exception("Arquivo não encontrado: {$file}");
    } catch (Exception $e) {
        echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px;'>";
        echo "<strong>Erro no Autoload:</strong><br>";
        echo "Classe: {$class}<br>";
        echo "Arquivo: {$file}<br>";
        echo "Erro: {$e->getMessage()}";
        echo "</div>";
        return false;
    }
}); 
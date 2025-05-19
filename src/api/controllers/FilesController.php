<?php

namespace CloudMoura\Api\Controllers;

use CloudMoura\Includes\Debug;

class FilesController extends Controller {

    private function formatFileSize($bytes) : string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function moveUp() : array | \Exception {
        try {
            $path = $this->postData['path'];
            $fullDirFile = DIR_UPLOAD . "/{$_SESSION['user']['id']}/{$path}";
            $fullDir = dirname($fullDirFile);
            $currentDir = dirname($path);
            $fileName = basename($path);

            // Primeiro tenta mover para o diretório à esquerda
            $parentDir = dirname($fullDir);
            $allDirs = glob($parentDir . "/*", GLOB_ONLYDIR);
            
            // Encontra o índice do diretório atual
            $currentIndex = array_search($fullDir, $allDirs);
            
            if ($currentIndex !== false && $currentIndex > 0) {
                // Se encontrou um diretório à esquerda, usa ele
                $newDir = $allDirs[$currentIndex - 1];
                $newPath = $newDir . "/" . $fileName;
            } else {
                // Se não houver diretório à esquerda, tenta mover para cima
                $newPath = $parentDir . "/" . $fileName;
                
                // Se o diretório pai for o diretório raiz do usuário, não pode mover para cima
                if ($parentDir === DIR_UPLOAD . "/{$_SESSION['user']['id']}") {
                    throw new \Exception("Não há diretórios disponíveis para mover o arquivo \"{$fileName}\"");
                }
            }

            if (file_exists($fullDirFile) && $fullDirFile !== $newPath) {
                if (rename($fullDirFile, $newPath)) {
                    return [ "message" => "Arquivo \"{$path}\" movido com sucesso." ];
                }
            }
            
            throw new \Exception("Erro ao mover o arquivo \"{$path}\"!");
        } catch (\Exception $e) {
            $this->debug->write($e->getMessage(), 'error_move_file');
            $this->lastError = $e->getMessage();
            throw new \Exception($this->lastError);
        }
    }

    public function moveDown() : array | \Exception {
        try {
            $path = $this->postData['path'];
            $fullDirFile = DIR_UPLOAD . "/{$_SESSION['user']['id']}/{$path}";
            $fullDir = dirname($fullDirFile);
            $currentDir = dirname($path);
            $fileName = basename($path);

            // Primeiro tenta encontrar um diretório abaixo
            $subDirs = glob($fullDir . "/*", GLOB_ONLYDIR);
            
            if (empty($subDirs)) {
                // Se não houver diretório abaixo, procura um diretório ao lado
                $parentDir = dirname($fullDir);
                $allDirs = glob($parentDir . "/*", GLOB_ONLYDIR);
                
                // Encontra o índice do diretório atual
                $currentIndex = array_search($fullDir, $allDirs);
                
                if ($currentIndex !== false && isset($allDirs[$currentIndex + 1])) {
                    // Se encontrou um diretório ao lado, usa ele
                    $newDir = $allDirs[$currentIndex + 1];
                } else {
                    throw new \Exception("Não há diretórios disponíveis para mover o arquivo \"{$fileName}\"");
                }
            } else {
                $newDir = $subDirs[0];
            }

            $newPath = $newDir . "/" . $fileName;

            if (file_exists($fullDirFile) && $fullDirFile !== $newPath) {
                if (rename($fullDirFile, $newPath)) {
                    return [ "message" => "Arquivo \"{$path}\" movido com sucesso." ];
                }
            }
            
            throw new \Exception("Erro ao mover o arquivo \"{$path}\"!");
        } catch (\Exception $e) {
            $this->debug->write($e->getMessage(), 'error_move_file');
            $this->lastError = $e->getMessage();
            throw new \Exception($this->lastError);
        }
    }

    public function deleteDir() : array | \Exception {
        try {
            $fullDir = DIR_UPLOAD . "/{$_SESSION['user']['id']}/{$this->postData['path']}";

            // Pega todos os arquivos e diretórios recursivamente
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($fullDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            $files = [];
            foreach ($iterator as $file) {
                $files[] = $file->getPathname();
            }

            // Ordena em ordem reversa para deletar do mais profundo para o mais superficial
            rsort($files);

            foreach ($files as $_k => $file) {
                if (is_dir($file)) {
                    rmdir($file);
                } else {
                    unlink($file);
                }
            }

            if (!rmdir($fullDir)) {
                $this->lastError = "Erro ao excluir o diretório \"{$this->postData['path']}\"!";
                $this->debug->write($this->lastError, 'error_delete_file');
                throw new \Exception($this->lastError);
            }

            return [ 'message' => "Diretório \"{$this->postData['path']}\" excluído com sucesso." ];
        } catch (\Exception $e) {
            $this->debug->write($e->getMessage(), 'error_delete_file');
            $this->lastError = $e->getMessage();
            return [ 'message' => $this->lastError ];
        }
    }

    public function deleteFile() : array | \Exception {
        try {
            $fullDir = DIR_UPLOAD . "/{$_SESSION['user']['id']}/{$this->postData['path']}";

            if (!unlink($fullDir)) {
                $this->lastError = "Erro ao excluir o arquivo \"{$this->postData['path']}\"!";
                $this->debug->write($this->lastError, 'error_delete_file');
                throw new \Exception($this->lastError);
            }

            return [ 'message' => "Arquivo \"{$this->postData['path']}\" excluído com sucesso." ];
        } catch (\Exception $e) {
            $this->debug->write($e->getMessage(), 'error_delete_file');
            $this->lastError = $e->getMessage();
            return [ 'message' => $this->lastError ];
        }
    }

    public function download() : array | \Exception {
        try {
            $fullDir = DIR_UPLOAD . "/{$_SESSION['user']['id']}/{$this->postData['path']}";

            if (!file_exists($fullDir)) {
                return [ 'message' => "Erro ao baixar o arquivo \"{$this->postData['path']}\"!" ];
            }

            // Lê o conteúdo do arquivo
            $content = file_get_contents($fullDir);
            if ($content === false) {
                return [ 'message' => "Erro ao baixar o arquivo \"{$this->postData['path']}\"!" ];
            }

            // Codifica o conteúdo em base64
            return [ 'message' => "Arquivo \"{$this->postData['path']}\" baixado com sucesso.", 'content' => base64_encode($content) ];
        } catch (\Exception $e) {
            $this->debug->write( $e->getMessage(), 'error_download_file' );
            $this->lastError = $e->getMessage();
            return [ 'message' => $this->lastError ];
        }
    }

    public function upload() : array | \Exception {
        try {
            $file = $this->postData['file'];
            $fileName = $this->postData['filename'] ?? 'arquivo_sem_nome';

            if ( empty( $this->postData['path'] ) ) {
                throw new \Exception( 'Diretório de destino não informado!' );
            }

            $targetDir = DIR_UPLOAD . "/{$_SESSION['user']['id']}/{$this->postData['path']}";
            
            // Decodifica o arquivo base64
            $fileContent = base64_decode($file);
            if ( $fileContent === false ) {
                throw new \Exception('Erro ao decodificar o arquivo base64');
            }

            // Usa o nome do arquivo enviado ou gera um nome único
            $fileName = $fileName ?: uniqid() . '.mp4';
            $targetPath = $targetDir . "/" . $fileName;
            if ( !file_exists( $targetDir ) ) {
                if ( !mkdir( $targetDir, 0775, true ) ) {
                    throw new \Exception("Erro ao criar o diretório \"{$this->postData['path']}\"!");
                }
            }

            // Salva o arquivo
            if ( file_put_contents($targetPath, $fileContent) !== false) {
                return [ 'message' => "Arquivo \"{$this->postData['path']}/{$fileName}\" enviado com sucesso." ];
            }

            throw new \Exception("Erro ao enviar o arquivo \"{$this->postData['path']}/{$fileName}\"!");
        } catch (\Exception $e) {
            $this->debug->write( $e->getMessage(), 'error_upload_file' );
            $this->lastError = $e->getMessage();
            return [ 'message' => $this->lastError ];
        }
    }

    public function createSubdirectory() : array | \Exception {
        $targetDir = DIR_UPLOAD . "/{$_SESSION['user']['id']}/{$this->postData['path']}";

        if ( file_exists( $targetDir ) ) {
            $this->lastError = "O diretório \"{$this->postData['path']}\" já existe!";
            $this->debug->write( $this->lastError, 'error_create_subdirectory' );
            throw new \Exception( $this->lastError );
        }

        if ( !mkdir( $targetDir, 0775, true ) ) {
            $this->lastError = "Erro ao criar o diretório \"{$this->postData['path']}\"!";
            $this->debug->write( $this->lastError, 'error_create_subdirectory' );
            throw new \Exception( $this->lastError );
        }

        chmod( $targetDir, 0775 );
        // chown( $targetDir, 'www-data');
        // chgrp( $targetDir, 'www-data');

        return [ 'message' => "Subdiretório \"{$this->postData['path']}\" criado com sucesso." ];
    }

    public function move() : array | \Exception {
        try {
            $path = $this->postData['path'];
            $targetDir = $this->postData['targetDir'];
            $type = $this->postData['type'] ?? 'file';
            
            $fullDirFile = DIR_UPLOAD . "/{$_SESSION['user']['id']}/{$path}";
            $fullTargetDir = DIR_UPLOAD . "/{$_SESSION['user']['id']}/{$targetDir}";
            $fileName = basename($path);
            $newPath = $fullTargetDir . "/" . $fileName;
            $this->debug->write("Tentando mover {$fullDirFile} para {$newPath}", 'move_item');

            // Verifica se o diretório de destino existe
            if (!file_exists($fullTargetDir)) {
                throw new \Exception("O diretório de destino não existe!");
            }

            // Verifica se o item a ser movido existe
            if (!file_exists($fullDirFile)) {
                throw new \Exception("O item a ser movido não existe!");
            }

            // Verifica se não está tentando mover para o mesmo lugar
            if ($fullDirFile === $newPath) {
                throw new \Exception("O item já está no diretório de destino!");
            }

            // Se for um diretório, verifica se não está tentando mover para dentro dele mesmo
            if ($type === 'dir' && strpos($newPath, $fullDirFile) === 0) {
                throw new \Exception("Não é possível mover um diretório para dentro dele mesmo!");
            }

            // Tenta mover o item
            if (rename($fullDirFile, $newPath)) {
                return [ "message" => "Item movido com sucesso." ];
            }
            
            throw new \Exception("Erro ao mover o item!");
        } catch (\Exception $e) {
            $this->debug->write($e->getMessage(), 'error_move_item');
            $this->lastError = $e->getMessage();
            throw new \Exception($this->lastError);
        }
    }
}
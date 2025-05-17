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
            $fullDirFile = DIR_UPLOAD . "/{$_SESSION['user']['id']}/{$this->postData['path']}";
            $fullDir = dirname($fullDirFile);
            $currentDir = dirname($this->postData['path']);
            $fileName = basename($this->postData['path']);

            $parentDir = dirname($fullDir);
            $newPath = $parentDir . "/" . $fileName;

            if (file_exists($fullDirFile) && $fullDirFile !== $newPath) {
                if (rename($fullDirFile, $newPath)) {
                    return [ "message" => "Arquivo \"{$this->postData['path']}\" movido com sucesso." ];
                }
            }

            throw new \Exception("Erro ao mover o arquivo \"{$this->postData['path']}\"!");
        } catch (\Exception $e) {
            $this->debug->write( $e->getMessage(), 'error_move_file' );
            $this->lastError = $e->getMessage();
            throw new \Exception( $this->lastError );
        }
    }

    public function moveDown() : array | \Exception {
        try {
            $path = $this->postData['path'];
            $fullDirFile = DIR_UPLOAD . "/{$_SESSION['user']['id']}/{$path}";
            $fullDir = dirname($fullDirFile);
            $currentDir = dirname($path);
            $fileName = basename($path);

            $subDirs = glob( $fullDir . "/*", GLOB_ONLYDIR);
            if ( empty($subDirs) ) {
                throw new \Exception("Não há diretórios abaixo de \"{$currentDir}\" para mover o arquivo \"{$fileName}\"");
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
            $this->debug->write( $e->getMessage(), 'error_move_file' );
            $this->lastError = $e->getMessage();
            throw new \Exception( $this->lastError );
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

            $targetDir = DIR_UPLOAD . "/{$_SESSION['user']['id']}/{$this->postData['path']}";
            if (!file_exists($targetDir)) {
                mkdir( $targetDir, 0775, true );
                chmod( $targetDir, 0775 );
                chown( $targetDir, 'www-data' );
                chgrp( $targetDir, 'www-data' );
            }
            
            // Decodifica o arquivo base64
            $fileContent = base64_decode($file);
            if ($fileContent === false) {
                throw new \Exception('Erro ao decodificar o arquivo base64');
            }

            // Usa o nome do arquivo enviado ou gera um nome único
            $fileName = $fileName ?: uniqid() . '.mp4';
            $targetPath = $targetDir . "/" . $fileName;
            
            // Salva o arquivo
            if (file_put_contents($targetPath, $fileContent) !== false) {
                return [ 'message' => "Arquivo \"{$this->postData['path']}/{$fileName}\" enviado com sucesso." ];
            }
            
            return [ 'message' => "Erro ao enviar o arquivo \"{$this->postData['path']}\"!" ];
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
        chown( $targetDir, 'www-data');
        chgrp( $targetDir, 'www-data');

        return [ 'message' => "Subdiretório \"{$this->postData['path']}\" criado com sucesso." ];
    }
}
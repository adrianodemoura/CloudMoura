<?php

namespace CloudMoura\Includes;

class Files {
    private Debug $debug;
    private string $lastError = "";

    public function __construct() {
        $this->debug = new Debug();
    }

    public function getLastError() : string {
        return $this->lastError;
    }

    private function formatFileSize($bytes) : string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function listDirectoryTree( $dir, $level = 0 ) : string {
        $html = '';
        $dirs = array();
        $files = array();

        if ( !file_exists( $dir ) ) { // Se o diretório não existir, cria o diretório
            mkdir( $dir . "/filmes", 0775, true );
            mkdir( $dir . "/series", 0775, true );
        }

        $allFiles = scandir($dir);

        foreach ($allFiles as $file) {
            if ($file == '.' || $file == '..') { continue; }
            $filePath = $dir . '/' . $file;

            if (is_dir($filePath)) {
                $dirs[] = $file;
            } else {
                $files[] = [
                    'name' => $file, 
                    'path' => $filePath,
                    'resumeDir' => str_replace( DIR_UPLOAD . "/{$_SESSION['user']['id']}/", "", $filePath )
                ];
            }
        }

        foreach ($dirs as $dirName) {
            $filePath = $dir . '/' . $dirName;
            $html .= $this->renderDivDir( $dir, $dirName, $level );
            $html .= $this->listDirectoryTree($filePath, $level + 1);
        }

        foreach ($files as $item) {
            $item = [
                'name' => $item['name'],
                'type' => 'file',
                'size' => $this->formatFileSize( filesize($item['path']) ),
                'modified' => filemtime($item['path']),
                'resumeDir' => $item['resumeDir']
            ];
            $html .= $this->renderDivFile( $item, $level );
        }

        return $html;
    }

    private function renderDivDir( $dir, $dirName, $level ) : string {
        $filePath = $dir . '/' . $dirName;
        $dirRaiz = str_replace( DIR_UPLOAD . "/{$_SESSION['user']['id']}/", "", $filePath );

        $html = "";
        $html .= str_repeat('_', $level * 1);
        $html .= "<i class='fas fa-folder text-tertiary me-2'></i> $dirName";
        $html .= "<a href='#' onclick='file(\"upload\", \"{$dirRaiz}\")' title='Enviar para o diretório \"{$dirName}\"' class='ms-2'>";
        $html .= "<i class='fas fa-upload text-secondary small'></i>";
        $html .= "<a href='#' onclick='file(\"createSubdirectory\", \"{$dirRaiz}\")' title='Criar Subdiretório abaixo de \"{$dirName}\"' class='ms-2'>";
        $html .= "<i class='fas fa-file-alt text-secondary small'></i>";
        $html .= "</a>";
        if ( !in_array( $dirName, [ "filmes", "series" ]     ) ) {
            $html .= "<a href='#' onclick='file(\"deleteDir\", \"{$dirRaiz}\")' title='Excluir diretório \"{$dirName}\"' class='ms-2'>";
            $html .= "<i class='fas fa-trash text-secondary small'></i> ";
            $html .= "</a>";
        }
        $html .= "<br />";

        return $html;
    }

    private function renderDivFile( $item, $level ) : string {
        $modified = ($item['type'] === 'file') ? date('d/m/Y H:i:s', $item['modified']) : '';

        $html = "";
        $html .= "<div>";
        $html .= str_repeat('_', $level * 1);

        if ( $level > 1 ) {
            $html .= "<a href='#' onclick='file(\"moveUp\", \"{$item['resumeDir']}\")' title='Mover para diretório superior' class='smallbtn btn-sm btn-info ms-2'>";
            $html .= "<i class='fas fa-arrow-up'></i>";
            $html .= "</a>";
        }
        $html .= "<a href='#' onclick='file(\"moveDown\", \"{$item['resumeDir']}\")' title='Mover para diretório inferior' class='smallbtn btn-sm btn-info ms-2'>";
        $html .= "<i class='fas fa-arrow-down'></i>";
        $html .= "</a>";
        $html .= "<a href='#' onclick='file(\"download\", \"{$item['resumeDir']}\")' title='Download' class='smallbtn btn-sm btn-primary ms-2'>";
        $html .= "<i class='fas fa-download'></i>";
        $html .= "</a>";
        $html .= "<a href='#' onclick='file(\"deleteFile\", \"{$item['resumeDir']}\")' title='Excluir' class='smallbtn btn-sm btn-danger ms-2'>";
        $html .= "<i class='fas fa-trash'></i>";
        $html .= "</a>";

        $html .= "<i class='fas fa-file text-secondary ms-2 me-2'></i>";
        $html .= "{$item['name']} (<span class='text-muted small ps-2'>{$item['size']} - {$modified}</span> )";
        $html .= "</div>";

        return $html;
    }

    public function move($dirFile, $direction) : bool {
        try {
            $fullDirFile = DIR_UPLOAD . "/{$_SESSION['user']['id']}/$dirFile";
            $fullDir = dirname($fullDirFile);
            $currentDir = dirname($dirFile);
            $fileName = basename($dirFile);

            if ($direction === "up") {
                $parentDir = dirname($fullDir);
                $newPath = $parentDir . "/" . $fileName;
            } else {
                $subDirs = glob( $fullDir . "/*", GLOB_ONLYDIR);
                if ( empty($subDirs) ) {
                    throw new \Exception("Não há diretórios abaixo de \"{$currentDir}\" para mover o arquivo \"{$fileName}\"");
                } else {
                    $newDir = $subDirs[0];
                }
                $newPath = $newDir . "/" . $fileName;
            }
            
            if (file_exists($fullDirFile) && $fullDirFile !== $newPath) {
                if (rename($fullDirFile, $newPath)) {
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            $this->debug->write( $e->getMessage(), 'error_move_file' );
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function delete( $path, $type ) : bool {
        try {
            $fullDir = DIR_UPLOAD . "/{$_SESSION['user']['id']}/$path";

            switch ( $type ) {
                case "dir":
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
                        $this->lastError = "Erro ao excluir o diretório \"{$path}\"!";
                        $this->debug->write($this->lastError, 'error_delete_file');
                        throw new \Exception($this->lastError);
                    }
                    break;
                case "file":
                    if (!unlink($fullDir)) {
                        $this->lastError = "Erro ao excluir o arquivo \"{$path}\"!";
                        $this->debug->write($this->lastError, 'error_delete_file');
                        throw new \Exception($this->lastError);
                    }
                    break;
            }

            return true;
        } catch (\Exception $e) {
            $this->debug->write($e->getMessage(), 'error_delete_file');
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function download($resumeDir) : string | bool {
        try {
            $fullDir = DIR_UPLOAD . "/{$_SESSION['user']['id']}/$resumeDir";

            if (!file_exists($fullDir)) {
                return false;
            }

            // Lê o conteúdo do arquivo
            $content = file_get_contents($fullDir);
            if ($content === false) {
                return false;
            }

            // Codifica o conteúdo em base64
            return base64_encode($content);
        } catch (\Exception $e) {
            $this->debug->write( $e->getMessage(), 'error_download_file' );
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function upload($path, $file, $filename = null) : bool {
        try {
            $targetDir = DIR_UPLOAD . "/{$_SESSION['user']['id']}/{$path}";
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            
            // Decodifica o arquivo base64
            $fileContent = base64_decode($file);
            if ($fileContent === false) {
                throw new \Exception('Erro ao decodificar o arquivo base64');
            }

            // Usa o nome do arquivo enviado ou gera um nome único
            $fileName = $filename ?: uniqid() . '.mp4';
            $targetPath = $targetDir . "/" . $fileName;
            
            // Salva o arquivo
            if (file_put_contents($targetPath, $fileContent) !== false) {
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            $this->debug->write( $e->getMessage(), 'error_upload_file' );
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function createSubdirectory( $path ) : bool {
        $targetDir = DIR_UPLOAD . "/{$_SESSION['user']['id']}/{$path}";

        if ( file_exists( $targetDir ) ) {
            $this->lastError = "O diretório \"{$path}\" já existe!";
            $this->debug->write( $this->lastError, 'error_create_subdirectory' );
            return false;
        }

        if ( !mkdir( $targetDir, 0775, true ) ) {
            $this->lastError = "Erro ao criar o diretório \"{$path}\"!";
            $this->debug->write( $this->lastError, 'error_create_subdirectory' );
            return false;
        }

        return true;
    }
}
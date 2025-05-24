<?php

namespace CloudMoura\Includes;

class Env {
    /**
     * Carrega múltiplos arquivos .env para $_ENV e putenv.
     * @param array $files Caminhos completos dos arquivos .env
     */
    public static function load(array $files) : void {
        foreach ( $files as $file ) {

            $file = DIR_ROOT . "/" . $file;

            if ( !file_exists($file) ) { continue; }

            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {

                $line = trim($line);

                if ($line === '' || $line[0] === '#') continue;

                if (strpos($line, '=') === false) continue;

                list($name, $value) = explode('=', $line, 2);

                $name = trim($name);

                $value = trim($value, " \t\n\r\0\x0B\"'");

                $_ENV[$name] = $value;

                putenv("$name=$value");
            }
        }
    }
}
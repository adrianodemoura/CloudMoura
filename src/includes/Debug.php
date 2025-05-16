<?php

namespace CloudMoura\Includes;

class Debug {
	public function write( $message = "", $type = "debug" ): void {

        if ( DEBUG === false ) { return; }

		$logFile = DIR_LOG . '/' . date('Y-m-d') . "_debug.log";
    	$timestamp = date('Y-m-d H:i:s') . '.' . substr(microtime(true), 11, 3);
    	$logMessage = "[$timestamp] [$type] $message" . PHP_EOL;

		try {
			file_put_contents($logFile, $logMessage, FILE_APPEND);
		} catch (\Throwable $th) {
			echo "Erro ao criar diretório de logs: " . $th->getMessage();
		}
	}
}
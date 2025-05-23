<?php

namespace CloudMoura\Includes;

class Logs {
	public function write( $message = "", $type = "info" ): void {
		$logFile = DIR_LOG . '/' . date('Y-m-d') . '.log';
    	$timestamp = date('Y-m-d H:i:s') . '.' . substr(microtime(true), 11, 3);
    	$logMessage = "[$timestamp] [$type] $message" . PHP_EOL;

		try {
			file_put_contents($logFile, $logMessage, FILE_APPEND);
		} catch (\Throwable $th) {
			echo "Erro ao criar diretório de logs: " . $th->getMessage();
		}
	}

	public function debug( $message = "", $type = "debug" ): void {

        if ( DEBUG === false ) { return; }

		$logFile = DIR_LOG . '/' . date('Y-m-d') . "_{$type}.log";
    	$timestamp = date('Y-m-d H:i:s') . '.' . substr(microtime(true), 11, 3);
    	$logMessage = "[$timestamp] $message" . PHP_EOL;

		try {
			file_put_contents($logFile, $logMessage, FILE_APPEND);
		} catch (\Throwable $th) {
			echo "Erro ao criar diretório de logs: " . $th->getMessage();
		}
	}
}
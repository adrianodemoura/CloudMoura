<?php

namespace CloudMoura\Includes;

class Logs {
	public function write( $message = "", $type = "info" ): void {
		$logFile = DIR_LOG . '/' . date('Y-m-d') . '.log';
    	$timestamp = date('Y-m-d H:i:s') . '.' . substr(microtime(true), 11, 3);
    	$logMessage = "[$timestamp] [$type] $message" . PHP_EOL;

		try {
			if ( !file_exists( DIR_LOG ) ) {
				mkdir(DIR_LOG, 0777, true);
				chown(DIR_LOG, 'www-data');
				chgrp(DIR_LOG, 'www-data');
			}
			file_put_contents($logFile, $logMessage, FILE_APPEND);
		} catch (\Throwable $th) {
			echo "Erro ao criar diretório de logs: " . $th->getMessage();
		}
	}
}
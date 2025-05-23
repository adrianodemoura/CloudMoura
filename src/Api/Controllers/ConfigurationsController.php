<?php

namespace CloudMoura\Api\Controllers;

use CloudMoura\Api\Controllers\Controller;

class ConfigurationsController extends Controller {

    public function toggledebug() : array {
        $config = json_decode( file_get_contents( DIR_ROOT . '/config.json' ), true );

        $config['debug'] = !$config['debug'];

        file_put_contents( DIR_ROOT . '/config.json', json_encode( $config, JSON_PRETTY_PRINT ) );

        return [ 'debug' => $config['debug'], 'message' => "Debug " . ( $config['debug'] ? 'ativado' : 'desativado' ) . " com sucesso." ];
    }

    public function toggleblocksite() : array {
        $config = json_decode(file_get_contents(DIR_ROOT . '/config.json'), true);

        $config['block'] = !$config['block'];

        file_put_contents( DIR_ROOT . '/config.json', json_encode( $config, JSON_PRETTY_PRINT ) );

        if ( $config['block'] ) {
            unset( $_SESSION['user'] );
            $this->Logs->debug( "Site bloqueado com sucesso.", "block" );
        } else {
            $this->Logs->debug( "Site desbloqueado com sucesso.", "block" );
        }

        return [ 'block' => $config['block'], 'message' => "Status do site " . ( $config['block'] ? 'bloqueado' : 'desbloqueado' ) . " com sucesso." ];
    }
}
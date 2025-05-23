<?php

namespace CloudMoura\Api\Includes;

class Response {
    public static function success($data = [] ) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(200);

        $message = !empty($data[ 'message' ]) ? $data[ 'message' ] : 'Operação realizada com sucesso.';
        unset( $data[ 'message' ] );

        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];

        exit( json_encode( $response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) );
    }
    
    public static function error(string $message, int $status = 400) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code( $status );

        $response = [
            'success' => false,
            'message' => $message
        ];

        exit( json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) );
    }
} 
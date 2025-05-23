<?php

namespace CloudMoura\Api\Includes;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use CloudMoura\Config\Mail;
use CloudMoura\Includes\Logs;

class Email {
    private $mailer;
    private Logs $Logs;

    public function __construct()
    {
        $config = Mail::getConfig();
        $this->Logs = new Logs();

        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host = $config['host'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $config['username'];
        $this->mailer->Password = $config['app_password'];
        $this->mailer->SMTPSecure = $config['smtp_secure'];
        $this->mailer->Port = $config['port'];
        $this->mailer->CharSet = 'UTF-8';
    }

    public function send($to, $subject, $body, $isHtml = true)
    {
        try {
            $this->mailer->setFrom( $this->mailer->Username );
            // $this->mailer->addReplyTo($from ?? $this->mailer->Username);
            // $this->mailer->addBCC($from ?? $this->mailer->Username);
            // $this->mailer->addCC($from ?? $this->mailer->Username);
            // $this->mailer->addBCC($to);
            // $this->mailer->addCC($to);
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->isHTML($isHtml);

            $envio = $this->mailer->send();
            if ( !$envio ) {
                throw new \Exception( "Erro ao enviar e-mail: " . $this->mailer->ErrorInfo );
            }
            $this->Logs->write( "E-mail enviado retorno: {\"{$envio}\"} com sucesso para \"" . $to . "\"", 'email' );
            return  true;
        } catch (Exception $e) {
            $this->Logs->write( "Erro ao enviar e-mail: " . $e->getMessage(), 'error' );
            return false;
        }
    }
}
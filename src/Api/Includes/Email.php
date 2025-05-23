<?php

namespace CloudMoura\Api\Includes;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use CloudMoura\Config\Mail;

class Email {
    private $mailer;

    public function __construct()
    {
        $config = Mail::getConfig();

        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host = $config['host'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $config['username'];
        $this->mailer->Password = $config['password'];
        $this->mailer->SMTPSecure = $config['smtp_secure'];
        $this->mailer->Port = $config['port'];
        $this->mailer->CharSet = 'UTF-8';
    }

    public function send($from, $to, $subject, $body, $isHtml = true)
    {
        try {
            $this->mailer->setFrom($from);
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->isHTML($isHtml);

            return $this->mailer->send();
        } catch (Exception $e) {
            return false;
        }
    }
}
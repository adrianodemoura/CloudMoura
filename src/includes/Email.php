<?php

namespace CloudMoura\Includes;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email
{
    private $mailer;

    public function __construct($host, $username, $password, $port = 587, $smtpSecure = 'tls')
    {
        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host = $host;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $username;
        $this->mailer->Password = $password;
        $this->mailer->SMTPSecure = $smtpSecure;
        $this->mailer->Port = $port;
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
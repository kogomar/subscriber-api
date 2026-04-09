<?php

namespace App\Infrastructure\Notification;

use App\Domain\Notification\NotifierInterface;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailNotifier implements NotifierInterface
{
    private string $host;
    private int $port;

    public function __construct()
    {
        $this->host = getenv('MAIL_HOST') ?: 'localhost';
        $this->port = (int)(getenv('MAIL_PORT') ?: 1025);
    }

    public function notify(string $email, string $repository, string $tag): void
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $this->host;
            $mail->Port = $this->port;
            $mail->SMTPAuth = false;

            $mail->setFrom('no-reply@octonotify.com', 'Notify');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "New release for {$repository}!";
            $mail->Body = "Hello!<br><br>A new version <b>{$tag}</b> has been released for <b>{$repository}</b>" .
                "<br><br>Check it out on GitHub!";

            $mail->send();
        } catch (Exception $e) {
            error_log("Email sending failed: {$mail->ErrorInfo}");
        }
    }
}

<?php
namespace App\Services;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailService
{

    public function __construct() {
        /* ... Classe Statica ... */ 
    }

    public static function sendEmailRecuperaSenha(MailerInterface $mailer, $destinatario, $link)
    {
        $email = (new Email())
            ->from('user@example.com')
            ->to($destinatario)
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Recuperação de Senha - Não responder esse email')
            ->text('Não responder esse email')
            ->html('
                <html>
                    <body>
                        <p>Olá, <br>
                          Para cadastrar uma nova senha acesse o link abaixo.</p>
                        <p><a href="'.$link.'">'.$link.'</a></p><br>
                        <p>Atenciosamente,<br>
                    </body>
                </html>');

        $mailer->send($email);

    }
}

?>
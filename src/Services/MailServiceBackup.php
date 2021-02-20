<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;

class MailServiceBackup {
    /*
    private $transport;
    private $username;
    private $password;
    private $host;
    private $port;
    private $encryption;
    private $authMode;
    CONFIGURADOS EM config/packages/swiftmailer.yaml
    */
    
    /*
    private $active = false;
    private $flashMessageBody = true;
    private $from = ['rtlayme@gmail.com' => 'Prismatis'];
    private $twig;
    private $mailer;
    private $request;
    private $flashMessages;
    
    
    public function __construct(\Swift_Mailer $mailer, Environment $twig, RequestStack $requestStack, \App\Services\FlashMessages $flashMessages) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->request = $requestStack->getCurrentRequest();
        $this->flashMessages = $flashMessages;
    }
    
    /*******************************************************************************************   MÉTODOS FIXOS   ***/
    
    /* PEGO AUTOMATICAMENTE PELAS CONFIGURAÇÕES DO SYMFONY
    protected function getTransport(){
        $settings = array(
            'auth' => RTL_MAIL_CONFIG_AUTH,
            'port' => RTL_MAIL_CONFIG_PORT,
            'username' => RTL_MAIL_CONFIG_EMAIL,
            'password' => RTL_MAIL_CONFIG_PASSWORD);
        return new Zend_Mail_Transport_Smtp(RTL_MAIL_CONFIG_HOST, $settings);
    }
     * 
     *
    
    public function send(\Swift_Message $message)
    {
        if($this->active)
            return $this->mailer->send($message);
        else if($this->flashMessageBody)
            $this->flashMessages->add($message->getBody(), 'i')->add('Conteúdo do e-mail', 'it');
        return;
    }
    
    /*****************************************************************************************   / MÉTODOS FIXOS   ***/
    /***********************************************************************************************   MENSAGENS   ***/
    /*
    public function sendMensagem (\App\Entity\Mensagens $mensagem, $to = null)
    {
        if(!$mensagem->getId())
            return;
        if(!$to)
            $to = [$mensagem->getDestinatario()->getEmail() => $mensagem->getDestinatario()->getNome()];
        
        $message = (new \Swift_Message($mensagem->getAssunto()))
            ->setFrom($this->from)
            ->setTo($to)
            ->setBody(
                $this->twig->render('_email/mensagem.html.twig', ['mensagem' => $mensagem]),
                'text/html'
            )
        ;
        $this->send($message);
    }
    
    public function sendEsqueciMinhaSenha(\App\Entity\Logins $login){
        if(!$login->getId())
            return;
        
        $message = (new \Swift_Message('Recupere seu acesso ao Prismatis'))
            ->setFrom($this->from)
            ->setTo([$login->getEmail() => $login->getNome()])
            ->setBody(
                $this->twig->render('_email/esqueci-minha-senha.html.twig', ['login' => $login]),
                'text/html'
            )
        ;
        $this->send($message);
    }    
    
    /***********************************************************************************************   MENSAGENS   ***/
    /*
    public static function sendAlterarEmail($login){
        $html = Rtl_Mail::getHtmlPartHeader();
        $html.='
            <p> Olá, <strong> ' . $login->get('nome') . '</strong> você alterou seu e-mail de acesso ao <b>Aguiar Sistema </b> e precisa confirmá-lo novamente. </p>
            <p> Acesse o link abaixo para confirmar seu e-mail e reativar seu acesso: </p>
            <br/>
            <p> <a href="' . Rtl_Mail::url('redefinir-senha', $login->get('id'), array('hash' => $login->get('hash'))) . '" title="Configure sua senha para acessar o Aguiar Sistema"> ' .Rtl_Mail::url('redefinir-senha', $login->get('id'), array('hash' => $login->get('hash'))) . ' </a> </p>
            <br/>
            <p> Depois que você informar uma nova senha, seu acesso será reestabelecido. </p>
        ';
        $html.= Rtl_Mail::getHtmlPartFooter();
        Rtl_Mail::_send($login->get('email'), $login->get('nome'), 'Alteração de e-mail', $html);
        $html = Rtl_Mail::getHtmlPartHeader();
        $html.='
            <h3> Solicitação de alteração de e-mail </h3>
            <p> Login: <b>'.$login->get('email').'</b> ID: <b>'.$login->get('id').'</b> Nome: <b>'.$login->get('nome').'</b></p>
        ';
        $html.= Rtl_Mail::getHtmlPartFooter();
        Rtl_Mail::_send(RTL_MAIL_CONFIG_EMAIL, RTL_MAIL_CONFIG_FROM, 'Alteração de e-mail', $html);
    }
    
    public static function sendAtendimento($data){
        $html = Rtl_Mail::getHtmlPartHeader();
        $html.='
            <p>Departamento: <b>' . $data['departamento'] . '</b></p>
            <p>De: <b>' . $data['nome'] . '</b></p>
            <p>E-mail: <b>' . $data['email'] . '</b></p>
            <p>Telefones: <b>' . $data['telefones'] . '</b></p>
            <p>Assunto: <b>' . $data['assunto'] . '</b></p>
            <p>Mensagem: </p>
            <p><b>' . nl2br($data['mensagem']) . '</b></p>
        ';
        $html.= Rtl_Mail::getHtmlPartFooter();
        Rtl_Mail::_send(RTL_MAIL_CONFIG_EMAIL, RTL_MAIL_CONFIG_FROM, 'Chamada de Atendimento', $html, null, $data['email'], $data['nome']);
    }
    
    */
}


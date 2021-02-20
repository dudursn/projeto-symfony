<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        if($error)
            $this->addFlash('d', $error->getMessageKey()); // poderia ser $error->getMessage()
        
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }
        
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername]);
    }

    /**
     * @Route("/recuperacao/{token}", name="app_recuperacao", methods={"GET","POST"})
     */
    public function recuperacao(\App\Mapper\LoginsMapper $loginsMapper, Request $request, $token=null): Response
    {
       
        if($token==null){
            return $this->redirectToRoute('app_login');
        }
        
        $result = $loginsMapper->select(["token_trocar_senha" => $token]);

        if (!$result) {
           return $this->redirectToRoute('app_login');
        }
               
        $form = $this->createForm(\App\Form\RecuperacaoType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                $data = $form->getData();
                $login = $result[0];

                if(($_login = $loginsMapper->savePass($login, $data['pass'])) instanceof \App\Entity\Logins) {
                    $loginsMapper->flash(['Nova senha registrada com sucesso!', 'Informe seu e-mail e senha para acessar a votação']);
                    return $this->redirectToRoute('app_login');
                } else {
                    $loginsMapper->flash($_usuario, 'd')->flash('Erros foram encontrados', 'dt');
                }
            } else {
                $loginsMapper->flash('Erros foram encontrados', 'd');
            }
        }

        return $this->render('security/recuperacao.html.twig', [
            'form' => $form->createView(),
        ]);
       
    }

    /**
     * @Route("/recupera_senha", name="app_recupera_senha", methods={"POST"})
     */
    public function recupera_senha(\App\Mapper\LoginsMapper $loginsMapper, Request $request, MailerInterface $mailer): Response
    {   
     
        
        $email = $request->request->get('email');

        if($email==null || trim($email) ==''){
            $loginsMapper->flash('Informe um e-mail válido para recuperar a senha', 'd');
        }

        $result = $loginsMapper->select(["email" => $email]) ;
      
        if (!$result) {
            $loginsMapper->flash('Não existe usuário cadastrado com esse e-mail', 'd');
            
        } else {

            $login = $result[0];
            $token = md5(session_id() . $login->getId() );
            
            if(($_login = $loginsMapper->saveTokenTrocarSenha($login, $token) instanceof \App\Entity\Logins)) {    

                /* Para enviar o email, verificar o domínio e configurar MAILER_DSN em .env antes
               
                $dominio = 'http://localhost:8000';
                $link = $dominio . '/recuperacao/' . $token;
                MailService::sendEmailRecuperaSenha($mailer, $login->getEmail(), $link);
                
                */

                $loginsMapper->flash(['Uma solicitação de nova senha foi enviada para o seu e-mail com sucesso!', 'Clique no link do e-mail e altere a sua senha']);
            } else {
                $loginsMapper->flash($_login, 'd')->flash('Erros foram encontrados', 'dt');
            }
            
        }

        return $this->redirectToRoute('app_login');       
             
    }
    


    /**
     * @Route("/registro", name="registro")
     */
    public function registro(\App\Mapper\LoginsMapper $loginsMapper, \App\Mapper\UsuariosMapper $usuariosMapper, \App\Mapper\CpfsPermitidosMapper $cpfsPermitidos, Request $request): Response
    {
        /*
        if (!$usuario = $usuariosMapper->find($id)) {
            $usuario = new \App\Entity\Usuarios();
            //$usuario->setRole('ROLE_USUARIO')->setAtivo(true);
        }
        */
        $form = $this->createForm(\App\Form\RegistroType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if(($_usuario = $usuariosMapper->saveRegistro($form, $loginsMapper, $cpfsPermitidos)) instanceof \App\Entity\Usuarios) {
                    $usuariosMapper->flash(['Registro concluído com sucesso!', 'Informe seu e-mail e senha para acessar a votação']);
                    return $this->redirectToRoute('app_login');
                } else {
                    $usuariosMapper->flash($_usuario, 'd')->flash('Erros foram encontrados', 'dt');
                }
            } else {
                $usuariosMapper->flash('Erros foram encontrados', 'd');
            }
        }
        
        return $this->render('security/registro.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}

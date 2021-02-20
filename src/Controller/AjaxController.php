<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api", name="api")
 */
class AjaxController extends AbstractController
{
    /**
     * @Route("/", name="", methods={"GET", "POST"})
     */
    public function index()
    {
        echo 'API OK';
        die();
        
        
        $flash->add ('Nome: ' . $this->getUser()->getNome());
        
            
        return $this->render('acesso/index.html.twig', [
            'controller_name' => 'AcessoController',
        ]);
    }
    
    /**
     * @Route("/logins", name="-logins")
     */
    public function pessoas(\App\Mapper\LoginsMapper $loginsMapper, Request $request)
    {
        //sleep(1);
        $json = array('ok' => 1, 'data' => array(), 'message' => '');
        
        $input = $request->request->get('input');
        
        foreach ($loginsMapper->select(['email_nome' => $input, 'limit' => 100], 'email') as $item) {
            $json['data'][] = array('value' => $item->getId(), 'label' => $item->getEmail() . ' (' . $item->getNome() . ')');
        }
        /*if(!$pessoasMapper->session()->get('loja'))
            $json = ['ok' => 2, 'data' => [], 'message' => 'Loja não identificada'];
         * 
         */
        return $this->json($json);
    }
    
    /**
     * @Route("/usuarios", name="-usuarios")
     */
    public function usuarios(\App\Mapper\UsuariosMapper $usuariosMapper, Request $request)
    {
        //sleep(1);
        $json = array('ok' => 1, 'data' => array(), 'message' => '');
        
        $input = $request->request->get('input');
        
        foreach ($usuariosMapper->select(['nome' => $input, 'limit' => 100], 'nome') as $item) {
            $json['data'][] = array('value' => $item->getId(), 'label' => $item->getNome() . ' (' . $item->view('doc') . ')');
        }
        /*if(!$pessoasMapper->session()->get('loja'))
            $json = ['ok' => 2, 'data' => [], 'message' => 'Loja não identificada'];
         * 
         */
        return $this->json($json);
    }
    
}

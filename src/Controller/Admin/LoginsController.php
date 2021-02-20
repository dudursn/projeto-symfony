<?php

namespace App\Controller\Admin;

use App\Entity\Logins;
use App\Form\LoginsPesquisaType;
use App\Form\LoginsType;
use App\Mapper\AssinantesMapper;
use App\Mapper\LoginsMapper;
use App\Services\BuscaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/admin/logins", name="admin-logins")
 */
class LoginsController extends AbstractController
{
    /**
     * @Route("/", name="", methods={"GET", "POST"})
     */
    public function index(LoginsMapper $loginsMapper, BuscaService $busca): Response
    {
        $form = $this->createForm(LoginsPesquisaType::class);
        $busca->setUp($form);
        /* PARA TIRAR O PARAMETRO "PAGE" DA URL, USAR O CÓDIGO ABAIXO
        if($busca->isPost())
            return $this->redirectToRoute($busca->getRequest()->get('_route'));
        */
        $paginator = $loginsMapper->select($busca->get(), array('id' => 'desc'), [$busca->getPage(), 10]);
        return $this->render('admin/logins/index.html.twig', [
            'form' => $form->createView(),
            'paginator' => $paginator,
        ]);
    }

    /**
     * @Route("/login/{id}", name="-login", methods={"GET","POST"})
     */
    public function login(LoginsMapper $loginsMapper, Request $request, $id = null): Response
    {
        if (!$login = $loginsMapper->find($id)) {
            $login = new Logins();
            $login->setRole('ROLE_USUARIO')->setAtivo(true);
        }
        
        $form = $this->createForm(LoginsType::class, $_login = clone $login);
        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if(($_login = $loginsMapper->save($login, $_login)) instanceof Logins) {
                    $loginsMapper->flash('Login salvo com sucesso');
                    return $this->redirectToRoute('admin-logins-login', ['id' => $_login->getId()]);
                } else {
                    $loginsMapper->flash($_login, 'd')->flash('Erros foram encontrados', 'dt');
                }
            } else {
                $loginsMapper->flash('Erros foram encontrados', 'd');
            }
        }
        
        return $this->render('admin/logins/login.html.twig', [
            'login' => $login,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/excluir/{id}", name="-excluir", methods={"GET", "POST"})
     */
//    public function excluir(LoginsMapper $loginsMapper, Request $request, $id)
//    {
//        //sleep(0);
//        $json = array('ok' => 1, 'ajax' => $request->query->get('ajax'), 'message' => '');
//        
//        if (!$login = $loginsMapper->find($id)) {
//            $json['ok'] = 2;
//            $json['message'] = 'Não foi possível identificar o Login';
//        } else {
//            if(!$this->isGranted('ROLE_ADMIN_EXCLUIR_PROTEGIDOS')) {
//                $json['ok'] = 2;
//                $json['message'] = 'Você não possui permissão para excluir esse Login';
//            }
//            if($json['ok'] === 1 && is_array($erros = $loginsMapper->delete($login))) {
//                $json['ok'] = 2;
//                $json['message'] = implode(', ', $erros);
//            }
//            if($json['ok'] === 1 && !$json['ajax']) {
//                $json['message'] = 'Login excluído com sucesso';
//            }
//        }
//        if($json['ajax'])
//            return $this->json($json);
//        $loginsMapper->flash($json['message'], $json['ok'] === 1?'s':'d');
//        return $this->redirectToRoute('admin-logins');        
//    }
    
}

<?php

namespace App\Controller\Admin;

use App\Entity\Usuarios;
use App\Form\UsuariosPesquisaType;
use App\Form\UsuariosType;
use App\Mapper\AssinantesMapper;
use App\Mapper\UsuariosMapper;
use App\Services\BuscaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/admin/usuarios", name="admin-usuarios")
 */
class UsuariosController extends AbstractController
{
    /**
     * @Route("/", name="", methods={"GET", "POST"})
     */
    public function index(UsuariosMapper $usuariosMapper, BuscaService $busca): Response
    {
        $form = $this->createForm(UsuariosPesquisaType::class);
        $busca->setUp($form);
        /* PARA TIRAR O PARAMETRO "PAGE" DA URL, USAR O CÓDIGO ABAIXO
        if($busca->isPost())
            return $this->redirectToRoute($busca->getRequest()->get('_route'));
        */
        $paginator = $usuariosMapper->select($busca->get(), array('id' => 'desc'), [$busca->getPage(), 10]);
        return $this->render('admin/usuarios/index.html.twig', [
            'form' => $form->createView(),
            'paginator' => $paginator,
        ]);
    }

    /**
     * @Route("/usuario/{id}", name="-usuario", methods={"GET","POST"})
     */
    public function usuario(UsuariosMapper $usuariosMapper, Request $request, $id = null): Response
    {
        if (!$usuario = $usuariosMapper->find($id)) {
            $usuario = new Usuarios();
            //$usuario->setRole('ROLE_USUARIO')->setAtivo(true);
        }
        
        $form = $this->createForm(UsuariosType::class, $_usuario = clone $usuario);
        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if(($_usuario = $usuariosMapper->save($usuario, $_usuario)) instanceof Usuarios) {
                    $usuariosMapper->flash('Usuário salvo com sucesso');
                    return $this->redirectToRoute('admin-usuarios-usuario', ['id' => $_usuario->getId()]);
                } else {
                    $usuariosMapper->flash($_usuario, 'd')->flash('Erros foram encontrados', 'dt');
                }
            } else {
                $usuariosMapper->flash('Erros foram encontrados', 'd');
            }
        }
        
        return $this->render('admin/usuarios/usuario.html.twig', [
            'usuario' => $usuario,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/excluir/{id}", name="-excluir", methods={"GET", "POST"})
     */
//    public function excluir(UsuariosMapper $usuariosMapper, Request $request, $id)
//    {
//        //sleep(0);
//        $json = array('ok' => 1, 'ajax' => $request->query->get('ajax'), 'message' => '');
//        
//        if (!$usuario = $usuariosMapper->find($id)) {
//            $json['ok'] = 2;
//            $json['message'] = 'Não foi possível identificar o Usuário';
//        } else {
//            if(!$this->isGranted('ROLE_ADMIN_EXCLUIR_PROTEGIDOS')) {
//                $json['ok'] = 2;
//                $json['message'] = 'Você não possui permissão para excluir esse Usuário';
//            }
//            if($json['ok'] === 1 && is_array($erros = $usuariosMapper->delete($usuario))) {
//                $json['ok'] = 2;
//                $json['message'] = implode(', ', $erros);
//            }
//            if($json['ok'] === 1 && !$json['ajax']) {
//                $json['message'] = 'Usuário excluído com sucesso';
//            }
//        }
//        if($json['ajax'])
//            return $this->json($json);
//        $usuariosMapper->flash($json['message'], $json['ok'] === 1?'s':'d');
//        return $this->redirectToRoute('admin-usuarios');        
//    }
    
}

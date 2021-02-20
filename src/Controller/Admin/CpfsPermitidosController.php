<?php

namespace App\Controller\Admin;

use App\Entity\CpfsPermitidos;
use App\Form\CpfsPermitidosPesquisaType;
use App\Form\CpfsPermitidosType;
use App\Mapper\CpfsPermitidosMapper;
use App\Services\BuscaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/admin/cpfs-permitidos", name="admin-cpfs")
 */
class CpfsPermitidosController extends AbstractController
{
    /**
     * @Route("/", name="", methods={"GET", "POST"})
     */
    public function index(CpfsPermitidosMapper $cpfsPermitidosMapper, BuscaService $busca): Response
    {
        $form = $this->createForm(CpfsPermitidosPesquisaType::class);
        $busca->setUp($form);
        
        $paginator = $cpfsPermitidosMapper->select($busca->get(), 'id', $busca->getPage());
        return $this->render('admin/cpfs-permitidos/index.html.twig', [
            'form' => $form->createView(),
            'paginator' => $paginator,
        ]);
    }

    /**
     * @Route("/cpf/{id}", name="-cpf", methods={"GET","POST"})
     */
    public function cpfPermitido(CpfsPermitidosMapper $cpfsPermitidosMapper, Request $request, $id = null): Response
    {
        if (!$cpfPermitido = $cpfsPermitidosMapper->find($id)) {
            $cpfPermitido = new CpfsPermitidos();
        }
        
        $form = $this->createForm(CpfsPermitidosType::class, $_cpfPermitido = clone $cpfPermitido);
        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $newEntity = $cpfPermitido->getId()?false:true;
                if(($_cpfPermitido = $cpfsPermitidosMapper->save($cpfPermitido, $_cpfPermitido)) instanceof CpfsPermitidos) {
                    $cpfsPermitidosMapper->flash('CPF ' . $_cpfPermitido->getCpfFormatado() . ' salvo com sucesso!');
                    if($newEntity) {
                        $cpfsPermitidosMapper->flash('Continue adicionando CPFs...');
                        return $this->redirectToRoute('admin-cpfs-cpf');
                    }
                    return $this->redirectToRoute('admin-cpfs');
                } else {
                    $cpfsPermitidosMapper->flash($_cpfPermitido, 'd')->flash('Erros foram encontrados', 'dt');
                }
            } else {
                $cpfsPermitidosMapper->flash('Erros foram encontrados', 'd');
            }
        }
        
        return $this->render('admin/cpfs-permitidos/cpf-permitido.html.twig', [
            'cpfPermitido' => $cpfPermitido,
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

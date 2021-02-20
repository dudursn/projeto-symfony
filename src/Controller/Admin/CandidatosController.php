<?php

namespace App\Controller\Admin;

use App\Entity\Candidatos;
use App\Form\CandidatosPesquisaType;
use App\Form\CandidatosType;
use App\Mapper\CandidatosMapper;
use App\Mapper\EleicoesMapper;
use App\Services\BuscaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/admin/candidatos", name="admin-candidatos")
 */
class CandidatosController extends AbstractController
{
    /**
     * @Route("/", name="", methods={"GET", "POST"})
     */
    public function index(CandidatosMapper $candidatosMapper, BuscaService $busca): Response
    {
        $form = $this->createForm(CandidatosPesquisaType::class);
        $busca->setUp($form);
        /* PARA TIRAR O PARAMETRO "PAGE" DA URL, USAR O CÓDIGO ABAIXO
        if($busca->isPost())
            return $this->redirectToRoute($busca->getRequest()->get('_route'));
        */
        $paginator = $candidatosMapper->select($busca->get(), array('id'), $busca->getPage());
        //$paginator = $candidatosMapper->select([], null, 1);
        return $this->render('admin/candidatos/index.html.twig', [
            'form' => $form->createView(),
            'paginator' => $paginator,
        ]);
    }

    /**
     * @Route("/candidato/{id}", name="-candidato", methods={"GET","POST"})
     */
    public function candidato(CandidatosMapper $candidatosMapper, EleicoesMapper $eleicoesMapper, Request $request, $id = null): Response
    {
        if (!$candidato = $candidatosMapper->find($id)) {
            $candidato = new Candidatos();
            $candidato->setEleicao($eleicoesMapper->find($request->query->getInt('eleicao')));
        }
        
        $form = $this->createForm(CandidatosType::class, $_candidato = clone $candidato);
        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if(($_candidato = $candidatosMapper->save($candidato, $_candidato)) instanceof Candidatos) {
                    $candidatosMapper->flash('Candidato salvo com sucesso');
                    return $this->redirectToRoute('admin-candidatos-candidato', ['id' => $_candidato->getId()]);
                } else {
                    $candidatosMapper->flash($_candidato, 'd')->flash('Erros foram encontrados', 'dt');
                }
            } else {
                $candidatosMapper->flash('Erros foram encontrados', 'd');
            }
        }
        
        return $this->render('admin/candidatos/candidato.html.twig', [
            'candidato' => $candidato,
            'form' => $form->createView(),
            'url' => urldecode($request->query->get('url')),
        ]);
    }

    /**
     * @Route("/excluir/{id}", name="-excluir", methods={"GET", "POST"})
     */
//    public function excluir(CandidatosMapper $candidatosMapper, Request $request, $id)
//    {
//        //sleep(0);
//        $json = array('ok' => 1, 'ajax' => $request->query->get('ajax'), 'message' => '');
//        
//        if (!$candidato = $candidatosMapper->find($id)) {
//            $json['ok'] = 2;
//            $json['message'] = 'Não foi possível identificar o Candidato';
//        } else {
//            if(!$this->isGranted('ROLE_ADMIN_EXCLUIR_PROTEGIDOS')) {
//                $json['ok'] = 2;
//                $json['message'] = 'Você não possui permissão para excluir esse Candidato';
//            }
//            if($json['ok'] === 1 && is_array($erros = $candidatosMapper->delete($candidato))) {
//                $json['ok'] = 2;
//                $json['message'] = implode(', ', $erros);
//            }
//            if($json['ok'] === 1 && !$json['ajax']) {
//                $json['message'] = 'Candidato excluído com sucesso';
//            }
//        }
//        if($json['ajax'])
//            return $this->json($json);
//        $candidatosMapper->flash($json['message'], $json['ok'] === 1?'s':'d');
//        return $this->redirectToRoute('admin-candidatos');        
//    }
    
}

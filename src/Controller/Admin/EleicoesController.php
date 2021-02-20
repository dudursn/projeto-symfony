<?php

namespace App\Controller\Admin;

use App\Entity\Eleicoes;
use App\Form\EleicoesPesquisaType;
use App\Form\EleicoesType;
use App\Mapper\AssinantesMapper;
use App\Mapper\EleicoesMapper;
use App\Services\BuscaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/admin/eleicoes", name="admin-eleicoes")
 */
class EleicoesController extends AbstractController
{
    /**
     * @Route("/", name="", methods={"GET", "POST"})
     */
    public function index(EleicoesMapper $eleicoesMapper, BuscaService $busca): Response
    {
        $form = $this->createForm(EleicoesPesquisaType::class);
        $busca->setUp($form);
        /* PARA TIRAR O PARAMETRO "PAGE" DA URL, USAR O CÓDIGO ABAIXO
        if($busca->isPost())
            return $this->redirectToRoute($busca->getRequest()->get('_route'));
        */
        $paginator = $eleicoesMapper->select($busca->get(), array('id' => 'desc'), [$busca->getPage(), 10]);
        return $this->render('admin/eleicoes/index.html.twig', [
            'form' => $form->createView(),
            'paginator' => $paginator,
        ]);
    }

    /**
     * @Route("/eleicao/{id}", name="-eleicao", methods={"GET","POST"})
     */
    public function eleicao(EleicoesMapper $eleicoesMapper, Request $request, $id = null): Response
    {
        if (!$eleicao = $eleicoesMapper->find($id)) {
            $eleicao = new Eleicoes();
            $eleicao->setAtivo(true);
        }
        
        $form = $this->createForm(EleicoesType::class, $_eleicao = clone $eleicao);
        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if(($_eleicao = $eleicoesMapper->save($eleicao, $_eleicao)) instanceof Eleicoes) {
                    $eleicoesMapper->flash('Eleição salva com sucesso');
                    return $this->redirectToRoute('admin-eleicoes-eleicao', ['id' => $_eleicao->getId()]);
                } else {
                    $eleicoesMapper->flash($_eleicao, 'd')->flash('Erros foram encontrados', 'dt');
                }
            } else {
                $eleicoesMapper->flash('Erros foram encontrados', 'd');
            }
        }
        
        return $this->render('admin/eleicoes/eleicao.html.twig', [
            'eleicao' => $eleicao,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/excluir/{id}", name="-excluir", methods={"GET", "POST"})
     */
//    public function excluir(EleicoesMapper $eleicoesMapper, Request $request, $id)
//    {
//        //sleep(0);
//        $json = array('ok' => 1, 'ajax' => $request->query->get('ajax'), 'message' => '');
//        
//        if (!$eleicao = $eleicoesMapper->find($id)) {
//            $json['ok'] = 2;
//            $json['message'] = 'Não foi possível identificar a Eleição';
//        } else {
//            if(!$this->isGranted('ROLE_ADMIN_EXCLUIR_PROTEGIDOS')) {
//                $json['ok'] = 2;
//                $json['message'] = 'Você não possui permissão para excluir essa Eleição';
//            }
//            if($json['ok'] === 1 && is_array($erros = $eleicoesMapper->delete($eleicao))) {
//                $json['ok'] = 2;
//                $json['message'] = implode(', ', $erros);
//            }
//            if($json['ok'] === 1 && !$json['ajax']) {
//                $json['message'] = 'Eleição excluída com sucesso';
//            }
//        }
//        if($json['ajax'])
//            return $this->json($json);
//        $eleicoesMapper->flash($json['message'], $json['ok'] === 1?'s':'d');
//        return $this->redirectToRoute('admin-eleicoes');        
//    }
    
}

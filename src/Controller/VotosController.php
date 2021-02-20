<?php

namespace App\Controller;

use App\Entity\Votos;
use App\Form\VotosPesquisaType;
use App\Form\VotosType;
use App\Mapper\VotosMapper;
use App\Mapper\EleicoesMapper;
use App\Services\BuscaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/votos", name="votos")
 */
class VotosController extends AbstractController
{
    /**
     * @Route("/", name="", methods={"GET", "POST"})
     */
    public function index(VotosMapper $votosMapper, \App\Mapper\UsuariosMapper $usuariosMapper, BuscaService $busca): Response
    {
        //$form = $this->createForm(VotosPesquisaType::class);
        //$busca->setUp($form);
        //$busca->set('usuario', $usuariosMapper->getLoginUsuario());
        /* PARA TIRAR O PARAMETRO "PAGE" DA URL, USAR O CÓDIGO ABAIXO
        if($busca->isPost())
            return $this->redirectToRoute($busca->getRequest()->get('_route'));
        */
        $paginator = $votosMapper->select(['usuario' => $usuariosMapper->getLoginUsuario()], array('id' => 'desc'), $busca->getPage());
        //$paginator = $votosMapper->select([], null, 1);
        return $this->render('votos/index.html.twig', [
            //'form' => $form->createView(),
            'paginator' => $paginator,
        ]);
    }

    /**
     * @Route("/voto/{id}", name="-voto", methods={"GET","POST"})
     */
    public function voto(VotosMapper $votosMapper, EleicoesMapper $eleicoesMapper, \App\Mapper\CandidatosMapper $candidatosMapper, \App\Mapper\UsuariosMapper $usuariosMapper, Request $request, $id = null): Response
    {
        if (!$voto = $votosMapper->find($id)) {
            $voto = new Votos();
            $voto->setEleicao($eleicoesMapper->find($request->query->getInt('eleicao')))
                    ->setUsuario($usuariosMapper->getLoginUsuario())
                    ->setCandidato($candidatosMapper->find($request->query->getInt('candidato')))
                    ;
            
            if(!$eleicoesMapper->eleicaoAtual()) {
                $eleicoesMapper->flash('Não votação em aberto no momento', 'd');
                return $this->redirectToRoute('index');
            }
            if(!$voto->getEleicao() || !$voto->getUsuario())
                return $this->redirectToRoute('index');
            if(!$request->query->get('votando')) {
                $request->query->set('votando', true);
                return $this->redirectToRoute('votos-voto', $request->query->all());
            }
        }
        
        if ($request->query->get('confirmar-voto')) {
            $oldVoto = clone $voto;
            $_voto = clone $voto;
            $_voto->setCandidato($candidatosMapper->find($request->query->get('candidato')));
            
            if(($_voto = $votosMapper->save($voto, $_voto)) instanceof Votos) {
                if(!$oldVoto->getId()) {
                    $eleicoesMapper->addVoto($voto->getEleicao());
                    $candidatosMapper->addVoto($_voto->getCandidato());
                }
                if($oldVoto->getId()) {
                    $candidatosMapper->addVoto($_voto->getCandidato());
                    $candidatosMapper->removeVoto($oldVoto->getCandidato());
                }
                $votosMapper->flash('Muito obrigado pelo seu voto!');
                return $this->redirectToRoute('index');
            } else {
                $votosMapper->flash($_voto, 'd')->flash('Erros foram encontrados', 'dt');
            }
            
        }
        
        /*$form = $this->createForm(VotosType::class, $_voto = clone $voto);
        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if(($_voto = $votosMapper->save($voto, $_voto)) instanceof Votos) {
                    $votosMapper->flash('Voto salvo com sucesso');
                    return $this->redirectToRoute('admin-votos-voto', ['id' => $_voto->getId()]);
                } else {
                    $votosMapper->flash($_voto, 'd')->flash('Erros foram encontrados', 'dt');
                }
            } else {
                $votosMapper->flash('Erros foram encontrados', 'd');
            }
        }
         * 
         */
        
        return $this->render('votos/voto.html.twig', [
            'voto' => $voto,
            //'form' => $form->createView(),
            'url' => urldecode($request->query->get('url')),
            'votando' => $request->query->get('votando'),
        ]);
    }

    /**
     * @Route("/excluir/{id}", name="-excluir", methods={"GET", "POST"})
     */
//    public function excluir(VotosMapper $votosMapper, Request $request, $id)
//    {
//        //sleep(0);
//        $json = array('ok' => 1, 'ajax' => $request->query->get('ajax'), 'message' => '');
//        
//        if (!$voto = $votosMapper->find($id)) {
//            $json['ok'] = 2;
//            $json['message'] = 'Não foi possível identificar o Voto';
//        } else {
//            if(!$this->isGranted('ROLE_ADMIN_EXCLUIR_PROTEGIDOS')) {
//                $json['ok'] = 2;
//                $json['message'] = 'Você não possui permissão para excluir esse Voto';
//            }
//            if($json['ok'] === 1 && is_array($erros = $votosMapper->delete($voto))) {
//                $json['ok'] = 2;
//                $json['message'] = implode(', ', $erros);
//            }
//            if($json['ok'] === 1 && !$json['ajax']) {
//                $json['message'] = 'Voto excluído com sucesso';
//            }
//        }
//        if($json['ajax'])
//            return $this->json($json);
//        $votosMapper->flash($json['message'], $json['ok'] === 1?'s':'d');
//        return $this->redirectToRoute('admin-votos');        
//    }
    
}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(\App\Mapper\EleicoesMapper $eleicoesMapper, \App\Mapper\VotosMapper $votosMapper, \App\Mapper\UsuariosMapper $usuariosMapper)
    {
        $jaVotou = false;
        $periodoDeVotacaoMsg = 1;
        if($eleicao = $eleicoesMapper->eleicaoAtual()) {
            $usuario = $usuariosMapper->getLoginUsuario();
            $votos = $votosMapper->select(['eleicao' => $eleicao, 'usuario' => $usuario]);
            if(count($votos))
                $jaVotou = true;
            $hoje = new \DateTime('today');
            if($hoje < $eleicao->getVotacaoInicio())
                $periodoDeVotacaoMsg = 2;
            if($hoje > $eleicao->getVotacaoFim())
                $periodoDeVotacaoMsg = 3;
            /*
             * PeriodoDeVotacaoMsg: 1 - Pode votar; 2 - Antes das Eleições; 3 - Já passou o período
             */
        }
        
        return $this->render('index/index.html.twig', [
            'jaVotou' => $jaVotou,
            'periodoDeVotacaoMsg' => $periodoDeVotacaoMsg,
        ]);
    }
    
    /**
     * @Route("/index/other-page", name="index-other-page")
     */
    public function otherPage()
    {
        //echo 'Other page, Bicth';
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }
}

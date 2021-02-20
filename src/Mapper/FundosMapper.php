<?php

namespace App\Mapper;

use App\Entity\Fundos;
use App\Mapper\AbstractMapper;
use App\Services\Constants;
use DateTime;
use App\Services\TextService;

class FundosMapper extends AbstractMapper
{
    // id, criacao, criador, criador_colaborador, edicao, editor, editor_colaborador, loja, nome, saldo_inicial, saldo_anterior, saldo, ordem, ativo, ativo_pagamentos, ativo_despesas, 
    protected $entityClass = Fundos::class;
    protected $selectFrom = 'f';
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($fundo, $data) { // Fundos 
        $this->checkEntityClass($fundo);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$fundo->getLoja())
            $errors[] = 'Loja não identificada';
        if(!$data->getNome())
            $errors[] = 'O campo Nome não pode estar vazio';
        
        if(count($this->select(['loja'=>$data->getLoja(), 'nome='=>$data->getNome(), 'id!='=>$data->getId()])))
            $errors[] = 'Um Fundo de Dinheiro com o mesmo Nome já foi cadastrado. Por favor, informe outro Nome para este Fundo';
        
        if(!$data->getAtivo())
            $data->setAtivoPagamentos($data->getAtivo())->setAtivoDespesas($data->getAtivo());
        
        if(count($errors)) {            
            return $errors;
        }
        
        $fundo->setNome($data->getNome())
                ->setOrdem($data->getOrdem())
                ->setAtivo($data->getAtivo())
                ->setAtivoPagamentos($data->getAtivoPagamentos())
                ->setAtivoDespesas($data->getAtivoDespesas())
                ;
        
        if($fundo->getId()) {
            $fundo->setEdicao(new DateTime())
                    ->setEditor($this->security->getUser())
                    ->setEditorColaborador($this->session->get('colaborador', null));
        } else {
            $fundo->setCriacao(new DateTime())
                    ->setCriador($this->security->getUser())
                    ->setCriadorColaborador($this->session->get('colaborador', null))
                    ->setSaldoInicial($data->getSaldoInicial())
                    ->setSaldoAnterior($data->getSaldoInicial())
                    ->setSaldo($data->getSaldoInicial());
        }
        
        return $this->_save($fundo);
    }

    public function select(array $atribs = array(), $orderBy = array(), $page = null, $join = array()) {
        $from = $this->selectFrom;
        $query = $this->repository->createQueryBuilder($from);
        /***********************************************************************************************   JOIN    ***/
        if(!is_array($join))
            $join = array($join);
        foreach ($join as $table) {
            if($table == 'novos_topos') {
                $novo_topo_from = 'nt';
                $query->innerJoin($from . '.novo_topo', $novo_topo_from)->addSelect($novo_topo_from);
            }
        }
        /*********************************************************************************************   / JOIN    ***/
        /**********************************************************************************************   WHERE    ***/
        foreach ($atribs as $atrib => $valor) {
            if ($atrib == 'id') {
                if ($valor != 0)
                    $query->andWhere($from . '.id = :id')->setParameter('id', (int)$valor);
            } else if ($atrib == 'id!=') {
                if ($valor != 0)
                    $query->andWhere($from . '.id != :id_diferente')->setParameter('id_diferente', (int)$valor);
            } else if ($atrib == 'loja') {
                if($valor instanceof \App\Entity\Lojas)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.loja = :loja')->setParameter('loja', (int)$valor);
            } else if ($atrib == 'nome=') {
                if ($valor != '')
                    $query->andWhere($from . '.nome = :nome_equal')->setParameter('nome_equal', $valor);
            } else if ($atrib == 'ativo') {
                if (is_numeric($valor))
                    $query->andWhere($from . '.ativo = :ativo')->setParameter('ativo', $valor);
            } else if ($atrib == 'ativo_pagamentos') {
                if (is_numeric($valor))
                    $query->andWhere($from . '.ativoPagamentos = :ativoPagamentos')->setParameter('ativoPagamentos', $valor);
            } else if ($atrib == 'ativo_despesas') {
                if (is_numeric($valor))
                    $query->andWhere($from . '.ativoDespesas = :ativoDespesas')->setParameter('ativoDespesas', $valor);
            } else if ($atrib == 'novo_topo_nome') {
                if ($valor != '')
                    $query->andWhere($novo_topo_from . '.nome like :' . $atrib)->setParameter($atrib, TextService::spaceToPercent($valor));
            } else if ($atrib == 'limit') {
                if (is_numeric($valor))
                    $query->setMaxResults($valor);
            } else {
                if ($valor != '')
                    $query->andWhere($from . '.' . $atrib . ' like :' . $atrib)->setParameter($atrib, TextService::spaceToPercent($valor));
            }
        }
        /********************************************************************************************   / WHERE    ***/
        return $this->_select($query, $orderBy, $page);
    }

    public function setDependencies($dependencies = array(), $entitys = array()) {
        /***/
    }

    /*************************************************************************************   / MÉTODOS ABSTRATOS   ***/
    
    /****************************************************************************************   REGRA DE NEGÓCIO   ***/
    
    
    
    /**************************************************************************************   / REGRA DE NEGÓCIO   ***/
    
}


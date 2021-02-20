<?php

namespace App\Mapper;

use App\Entity\Itens;
use App\Entity\ItensAjustes;
use App\Mapper\AbstractMapper;
use App\Services\DateService;
use App\Services\TextService;
use DateTime;

class ItensAjustesMapper extends AbstractMapper
{
    // 'id', 'criacao', 'criador', 'criador_colaborador', 'edicao', 'editor', 'editor_colaborador', 'item', 'data', 'tipo', 'qtd', 'justificativa'
    protected $entityClass = ItensAjustes::class;
    protected $selectFrom = 'ia';
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($itemAjuste, $data) { // ItensAjustes 
        $this->checkEntityClass($itemAjuste);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        // id', 'criacao', 'criador', 'criador_colaborador', 'edicao', 'editor', 'editor_colaborador', 
        if(!$itemAjuste->getItem())
            $errors[] = 'Item não identificado';
        if(!$data->getData())
            $errors[] = 'O campo Data não pode estar vazio';
        if(!$data->getTipo())
            $errors[] = 'O campo Fluxo não pode estar vazio';
        if((int)$data->getQtd() <= 0)
            $errors[] = 'O campo Quantidade não pode estar vazio';
        if(!$data->getJustificativa())
            $errors[] = 'O campo Justificativa não pode estar vazio';
        
        if(count($errors)) {            
            return $errors;
        }
        
        $itemAjuste->setData($data->getData())
                ->setTipo($data->getTipo())
                ->setQtd($data->getQtd())
                ->setJustificativa($data->getJustificativa())
                ;
        
        if($itemAjuste->getId()) {
            $itemAjuste->setEdicao(new DateTime())
                    ->setEditor($this->security->getUser())
                    ->setEditorColaborador($this->session->get('colaborador', null));
        } else {
            $itemAjuste->setCriacao(new DateTime())
                    ->setCriador($this->security->getUser())
                    ->setCriadorColaborador($this->session->get('colaborador', null));
        }
        
        return $this->_save($itemAjuste);
    }

    public function select(array $atribs = array(), $orderBy = array(), $page = null, $join = array()) {
        $from = $this->selectFrom;
        $query = $this->repository->createQueryBuilder($from);
        /***********************************************************************************************   JOIN    ***/
        if(!is_array($join))
            $join = array($join);
        foreach ($join as $table) {
            if($table == 'itens') {
                $itens_from = 'i';
                $query->innerJoin($from . '.item', $itens_from)->addSelect($itens_from);
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
            } else if ($atrib == 'item') {
                if($valor instanceof Itens)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.item = :item')->setParameter('item', (int)$valor);
            } else if ($atrib == 'data_de') {
                if ($valor != '')
                    $query->andWhere($from . '.data >= :data_de')->setParameter('data_de', DateService::converte($valor));
            } else if ($atrib == 'data_ate') {
                if ($valor != '')
                    $query->andWhere($from . '.data <= :data_ate')->setParameter('data_ate', DateService::converte($valor));
            } else if ($atrib == 'limit') {
                if (is_numeric($valor))
                    $query->setMaxResults($valor);
            } else if ($atrib == 'item_loja') {
                if($valor instanceof \App\Entity\Lojas)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($itens_from . '.loja = :item_loja')->setParameter('item_loja', (int)$valor);
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


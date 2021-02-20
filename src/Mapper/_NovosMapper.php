<?php

namespace App\Mapper;

use App\Entity\Novos;
use App\Entity\NovosDependencias;
use App\Mapper\AbstractMapper;
use App\Services\TextService;

class _NovosMapper extends AbstractMapper
{
    // id, criacao, criador, edicao, editor, nome, atrib1, atrib2, ...
    protected $entityClass = Novos::class;
    protected $selectFrom = 'n';
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($novo, $data) {
        $this->checkEntityClass($novo);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(count($this->select(['nome='=>$data->getNome(), 'uf'=>$data->getUf(), 'id!='=>$novo->getId()])))
            $errors[] = 'Uma novo já foi cadastrado com esse nome';
        
        if(count($errors)) {
            return $errors;
        }
        
        $novo->setAtrib($data->getAtrib())->setAtrib($data->getAtrib());
        
        // Remover depois
        $loginLogado = $this->loginsMapper->find(1);
        // / Remover depois
        
        if($novo->getId()) {
            $novo->setEdicao(new \DateTime())->setEditor($loginLogado);
        } else {
            $novo->setCriacao(new \DateTime())->setCriador($loginLogado);
            if(1 == 1) //Se user for ADMINSTRADOR OU OPERACIONAl ... REMOVER DEPOIS
                $novo->setConfirmadoLogin($loginLogado)->setConfirmadoDatahora(new \DateTime());
        }
        
        return $this->_save($novo);
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
            } else if ($atrib == 'uf') {
                if($valor instanceof Ufs)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.uf = :uf')->setParameter('uf', (int)$valor);
            } else if ($atrib == 'nome=') {
                if ($valor != '')
                    $query->andWhere($from . '.nome = :nome')->setParameter('nome', $valor);
            } else if ($atrib == 'ativo') {
                if (is_numeric($valor))
                    $query->andWhere($from . '.ativo = :ativo')->setParameter('ativo', (int)$valor);
            } else if ($atrib == 'novo_topo_nome') {
                if ($valor != '')
                    $query->andWhere($novo_topo_from . '.nome like :' . $atrib)->setParameter($atrib, TextService::spaceToPercent($valor));
            } else {
                if ($valor != '')
                    $query->andWhere($from . '.' . $atrib . ' like :' . $atrib)->setParameter($atrib, TextService::spaceToPercent($valor));
            }
        }
        /********************************************************************************************   / WHERE    ***/
        return $this->_select($query, $orderBy, $page);
    }

    public function setDependencies($dependencies = array(), $entitys = array()) {
        /* Comentar código caso Mapper não seja dependente de nenhum outro Mapper */
        /*
        if(!is_array($dependencies) && $dependencies)
            $dependencies = array($dependencies);
        else if(!$dependencies)
            $dependencies = array();
        foreach ($dependencies as $dependency) {
            if($dependency instanceof NovosToposMapper)
                $this->novosMapper = $dependency;
        }
        
        if(!is_array($entitys) && $entitys)
            $entitys = array($entitys);
        else if(!$entitys)
            $entitys = array();
        foreach ($entitys as $entity) {
            if(($entity instanceof NovosTopos) && $entity->getId())
                $entity->setnNovosMapper($this);
        }
        
        return $this;
        */
    }

    /*************************************************************************************   / MÉTODOS ABSTRATOS   ***/
    
    /******************************************************************************************   REGRA DE NEGÓCIO   ***/
    
    // ... 
    
    /****************************************************************************************   / REGRA DE NEGÓCIO   ***/
    
}


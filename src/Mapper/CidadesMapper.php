<?php

namespace App\Mapper;

use App\Entity\Cidades;
use App\Entity\Ufs;
use App\Mapper\AbstractMapper;
use App\Services\TextService;

class CidadesMapper extends AbstractMapper
{
    // id, criacao, criador, edicao, editor, uf, nome, ordem, ativo, confirmado_login, confirmado_datahora
    protected $entityClass = Cidades::class;
    protected $selectFrom = 'c';
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($cidade, $data) {
        $this->checkEntityClass($cidade);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(count($this->select(['nome='=>$data->getNome(), 'uf'=>$data->getUf(), 'id!='=>$cidade->getId()])))
            $errors[] = 'Uma cidade já foi cadastrada com esse nome';
        
        if(count($errors)) {
            return $errors;
        }
        
        $cidade->setUf($data->getUf())->setNome($data->getNome())->setOrdem($data->getOrdem())->setAtivo($data->getAtivo());
        
        if($cidade->getId()) {
            $cidade->setEdicao(new \DateTime())->setEditor($this->security->getUser());
        } else {
            $cidade->setCriacao(new \DateTime())->setCriador($this->security->getUser());
            if($this->security->isGranted('ROLE_OPERADOR'))
                $cidade->setConfirmadoLogin($this->security->getUser())->setConfirmadoDatahora(new \DateTime());
        }
        
        return $this->_save($cidade);
    }

    public function select(array $atribs = array(), $orderBy = array(), $page = null, $join = array()) {
        $from = $this->selectFrom;
        $query = $this->repository->createQueryBuilder($from);
        /***********************************************************************************************   JOIN    ***/
        if(!is_array($join))
            $join = array($join);
        foreach ($join as $table) {
            if($table == 'ufs') {
                $uf_from = 'u';
                $query->innerJoin($from . '.uf', $uf_from)->addSelect($uf_from);
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
            } else if ($atrib == 'limit') {
                if (is_numeric($valor))
                    $query->setMaxResults($valor);
            } else if ($atrib == 'uf_sigla') {
                if ($valor != '')
                    $query->andWhere($uf_from. '.sigla like :' . $atrib)->setParameter($atrib, TextService::spaceToPercent($valor));
            } else {
                if ($valor != '')
                    $query->andWhere($from . '.' . $atrib . ' like :' . $atrib)->setParameter($atrib, TextService::spaceToPercent($valor));
            }
        }
        /********************************************************************************************   / WHERE    ***/
        return $this->_select($query, $orderBy, $page);
    }

    public function setDependencies($dependencies = array(), $entitys = array()) {
        if(!is_array($dependencies) && $dependencies)
            $dependencies = array($dependencies);
        else if(!$dependencies)
            $dependencies = array();
        foreach ($dependencies as $dependency) {
            if($dependency instanceof LoginsMapper)
                $this->loginsMapper = $dependency;
        }
        
        if(!is_array($entitys) && $entitys)
            $entitys = array($entitys);
        else if(!$entitys)
            $entitys = array();
        foreach ($entitys as $entity) {
            if(($entity instanceof Ufs) && $entity->getId())
                $entity->setCidadesMapper($this);
        }
        
        return $this;
    }

    /*************************************************************************************   / MÉTODOS ABSTRATOS   ***/
    
    /******************************************************************************************   REGRA DE NEGÓCIO   ***/
    
    // ... 
    
    /****************************************************************************************   / REGRA DE NEGÓCIO   ***/
    
}


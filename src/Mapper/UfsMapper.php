<?php

namespace App\Mapper;

use App\Entity\Ufs;
use App\Mapper\AbstractMapper;
use App\Services\TextService;

class UfsMapper extends AbstractMapper
{
    // id, criacao, nome, sigla, ordem, 
    protected $entityClass = Ufs::class;
    protected $selectFrom = 'u';
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($uf, $data) {
        $this->checkEntityClass($uf);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$uf->getId())
            $errors[] = 'Não é possível adicionar uma nova UF';
        
        if(count($errors))
            return $errors;
        
        //$uf->setNome($data->getNome())->setSigla($data->getSigla())->setOrdem($data->getOrdem());
        $uf->setOrdem($data->getOrdem());
        
        if($uf->getId()) {
            //$uf->setEdicao(new DateTime())->setEditor($sessionLogin);
        } else {
            $uf->setCriacao(new DateTime());
        }
        
        return $this->_save($uf);
    }

    public function select(array $atribs = array(), $orderBy = array(), $page = null, $join = array()) {
        $from = $this->selectFrom;
        $query = $this->repository->createQueryBuilder($from);
        /***********************************************************************************************   JOIN    ***/
        if(!is_array($join))
            $join = array($join);
        foreach ($join as $table) {
            if($table == 'criador_logins') {
                $login_from = 'll';
                $query->innerJoin($from . '.criador', $login_from)->addSelect($login_from);
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
            } else {
                if ($valor != '')
                    $query->andWhere($from . '.' . $atrib . ' like :' . $atrib)->setParameter($atrib, TextService::spaceToPercent($valor));
            }
        }
        /********************************************************************************************   / WHERE    ***/
        return $this->_select($query, $orderBy, $page);
    }
    
    protected function setDependencies($dependencies = array(), $entitys = array()) {
        //Sem dependencias...
        return $this;
    }

    /*************************************************************************************   / MÉTODOS ABSTRATOS   ***/
    
    /******************************************************************************************   REGRA DE NEGÓCIO   ***/
    
    // ... 
    
    /****************************************************************************************   / REGRA DE NEGÓCIO   ***/
    
}


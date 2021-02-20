<?php

namespace App\Mapper;

use App\Entity\CpfsPermitidos;
use App\Services\TextService;


class CpfsPermitidosMapper extends AbstractMapper
{
    // id, criacao, criador, edicao, editor, email, pass, nome, role, hash, hash_criacao, confirmado, ativo, 
    protected $entityClass = CpfsPermitidos::class;
    protected $selectFrom = 'cp';
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($cpfPermitido, $data) { // CpfsPermitidos 
        $this->checkEntityClass($cpfPermitido);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$data->getCpf())
            $errors[] = 'Por favor, informe um CPF Válido';
        
        if(count($this->select(['cpf='=>$data->getCpf(), 'id!='=>$cpfPermitido->getId()])))
            $errors[] = 'O CPF já foi cadastrado. Por favor, informe outro CPF para Permissão de Registro';
        
        if(count($errors)) {
            return $errors;
        }
        
        $cpfPermitido->setCpf($data->getCpf());
        
        /*
        if($login->getId()) {
            $login->setEdicao(new DateTime())->setEditor($this->security->getUser());
        } else {
            $login->setCriacao(new DateTime())->setCriador($this->security->getUser());
        }
         * 
         */
        
        return $this->_save($cpfPermitido);
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
            } else if ($atrib == 'criador') {
                if($valor instanceof Logins)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.criador = :criador')->setParameter('criador', (int)$valor);
            } else if ($atrib == 'cpf=') {
                if ($valor != '')
                    $query->andWhere($from . '.cpf = :cpf')->setParameter('cpf', $valor);
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
    
    protected function setDependencies($dependencies = array(), $entitys = array()) {
        //Sem dependencias...
        return $this;
    }

    /*************************************************************************************   / MÉTODOS ABSTRATOS   ***/
    
    /******************************************************************************************   REGRA DE NEGÓCIO   ***/
    
    

    /****************************************************************************************   / REGRA DE NEGÓCIO   ***/
    
}


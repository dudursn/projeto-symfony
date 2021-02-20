<?php

namespace App\Mapper;

use App\Entity\LoginsColaboradores;
use App\Entity\Logins;
use App\Entity\Colaboradores;
use App\Mapper\AbstractMapper;
use App\Services\TextService;
use DateTime;

class LoginsColaboradoresMapper extends AbstractMapper
{
    // 'id', 'criacao', 'criador', 'login', 'colaborador', 'padrao', 'favorito', 'ordem', 'contador'
    protected $entityClass = LoginsColaboradores::class;
    protected $selectFrom = 'lc';
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($loginColaborador, $data) { // LoginsColaboradores
        $this->checkEntityClass($loginColaborador);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$loginColaborador->getLogin())
            $errors[] = 'Login não identificado';
        if(!$loginColaborador->getColaborador())
            $errors[] = 'Colaborador não identificado';
        if(count($this->select(['login'=>$loginColaborador->getLogin(), 'colaborador'=>$loginColaborador->getColaborador(), 'id!='=>$loginColaborador->getId()])))
            $errors[] = 'Este e-mail já possui acesso para este Colaborador';
        
        if(count($errors)) {
            return $errors;
        }
        
        $loginColaborador->setPadrao($data->getPadrao())
                ->setFavorito($data->getFavorito())
                ->setOrdem($data->getOrdem())
                ->setContador($data->getContador())
                ;
        
        if($loginColaborador->getId()) {
            //$loginColaborador->setEdicao(new DateTime())->setEditor($this->security->getUser());
        } else {
            $loginColaborador->setCriacao(new DateTime())->setCriador($this->security->getUser());
        }
        return $this->_save($loginColaborador);
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
            } else if ($atrib == 'login') {
                if($valor instanceof Logins)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.login = :login')->setParameter('login', (int)$valor);
            } else if ($atrib == 'colaborador') {
                if($valor instanceof Colaboradores)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.colaborador = :colaborador')->setParameter('colaborador', (int)$valor);
            } else if ($atrib == 'nome=') {
                if ($valor != '')
                    $query->andWhere($from . '.nome = :nome')->setParameter('nome', $valor);
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
    
    /*
     * Verifica se o Login passado tem o Colaborador passado em seu roll de LoginsColaboradores
     */
    public function existeColaborador(Logins $login, Colaboradores $colaborador): bool
    {
        if(!$login->getId() || !$colaborador->getId())
            return false;
        if(count($this->select(['login' => $login, 'colaborador' => $colaborador])))
            return true;
        return false;
    }

    public function hasLogin() {
        
    }
    
    /****************************************************************************************   / REGRA DE NEGÓCIO   ***/
    
}


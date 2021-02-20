<?php

namespace App\Mapper;

use App\Entity\Cidades;
use App\Entity\Votos;
use App\Entity\Lojas;
use App\Entity\Ufs;
use App\Mapper\AbstractMapper;
use App\Services\FilesService;
use App\Services\TextService;
use DateTime;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class VotosMapper extends AbstractMapper
{
    // id, criacao, criador, edicao, editor, eleicao, usuario, candidato
    protected $entityClass = Votos::class;
    protected $selectFrom = 'v';
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($voto, $data) { // Votos 
        $this->checkEntityClass($voto);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$voto->getEleicao()->getAtivo())
            $errors[] = 'Não foi possível computar o voto. Eleição não está ativa';
        $hoje = new \DateTime('today');
        if($hoje < $voto->getEleicao()->getVotacaoInicio())
            $errors[] = 'Não foi possível computar o voto. O período de votação ainda não iniciou';
        if($hoje > $voto->getEleicao()->getVotacaoFim())
            $errors[] = 'Não foi possível computar o voto. O período de votação já encerrou';
            
        if(count($this->select(['eleicao' => $voto->getEleicao(), 'usuario' => $voto->getUsuario(), 'id!='=>$voto->getId()])))
            $errors[] = 'Você já votou nessa eleição!';
        
        if(count($errors)) {
            return $errors;
        }
        
        $voto->setCandidato($data->getCandidato());
        
        if($voto->getId()) {
            $voto->setEdicao(new DateTime())->setEditor($this->security->getUser());
        } else {
            $voto->setCriacao(new DateTime())->setCriador($this->security->getUser());
        }
        
        return $this->_save($voto);
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
            } else if ($atrib == 'eleicao') {
                if($valor instanceof \App\Entity\Eleicoes)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.eleicao = :eleicao')->setParameter('eleicao', (int)$valor);
            } else if ($atrib == 'usuario') {
                if($valor instanceof \App\Entity\Usuarios)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.usuario = :usuario')->setParameter('usuario', (int)$valor);
            } else if ($atrib == 'apelido=') {
                if ($valor != '')
                    $query->andWhere($from . '.apelido = :apelido')->setParameter('apelido', $valor);
            } else if ($atrib == 'mandato=') {
                if ($valor != '')
                    $query->andWhere($from . '.mandato = :mandato')->setParameter('mandato', $valor);
            } else if ($atrib == 'numero=') {
                if ($valor != '')
                    $query->andWhere($from . '.numero = :numero')->setParameter('numero', $valor);
            } else if ($atrib == 'ativo') {
                if (is_numeric($valor))
                    $query->andWhere($from . '.ativo = :ativo')->setParameter('ativo', (int)$valor);
            } else if ($atrib == 'limit') {
                if (is_numeric($valor))
                    $query->setMaxResults($valor);
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
        if(!is_array($dependencies) && $dependencies)
            $dependencies = array($dependencies);
        else if(!$dependencies)
            $dependencies = array();
        foreach ($dependencies as $key => $dependency) {
            if($key == 'protectedPath')
                $this->protectedPath = $dependency;
            else if($key == 'publicPath')
                $this->publicPath = $dependency;
            /*
            if($dependency instanceof NovosToposMapper)
                $this->novosMapper = $dependency;
            */
        }
        /*
        if(!is_array($entitys) && $entitys)
            $entitys = array($entitys);
        else if(!$entitys)
            $entitys = array();
        foreach ($entitys as $entity) {
            if(($entity instanceof NovosTopos) && $entity->getId())
                $entity->setnNovosMapper($this);
        }
        */
        return $this;
    }

    /*************************************************************************************   / MÉTODOS ABSTRATOS   ***/
    
    /******************************************************************************************   REGRA DE NEGÓCIO   ***/
    
    /****************************************************************************************   / REGRA DE NEGÓCIO   ***/
    
}


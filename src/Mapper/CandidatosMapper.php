<?php

namespace App\Mapper;

use App\Entity\Cidades;
use App\Entity\Candidatos;
use App\Entity\Lojas;
use App\Entity\Ufs;
use App\Mapper\AbstractMapper;
use App\Services\FilesService;
use App\Services\TextService;
use DateTime;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CandidatosMapper extends AbstractMapper
{
    // id, criacao, criador, edicao, editor, eleicao, usuario, apelido, mandato, numero, info, votosQtd
    protected $entityClass = Candidatos::class;
    protected $selectFrom = 'c';
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($candidato, $data) { // Candidatos 
        $this->checkEntityClass($candidato);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$candidato->getEleicao())
            $errors[] = 'Eleição não identificada';
        if(!$data->getUsuario())
            $errors[] = 'O campo Usuário não pode estar vazio';
        if(!$data->getApelido())
            $errors[] = 'O campo Apelido não pode estar vazio';
        if(!$data->getNumero())
            $errors[] = 'O campo Número não pode estar vazio';
        
        if(count($this->select(['eleicao' => $candidato->getEleicao(), 'apelido='=>$data->getApelido(), 'id!='=>$candidato->getId()])))
            $errors[] = 'Apelido já selecionado para esta eleição. Por favor, informe um outro Apelido para o Candidato';
        if(count($this->select(['eleicao' => $candidato->getEleicao(), 'mandato='=>$data->getMandato(), 'numero='=>$data->getNumero(), 'id!='=>$candidato->getId()])))
            $errors[] = 'Número já selecionado para esta eleição. Por favor, informe um outro Número para o Candidato';
        
        if(count($errors)) {
            return $errors;
        }
        
        $candidato->setUsuario($data->getUsuario())
                ->setApelido($data->getApelido())
                ->setMandato($data->getMandato())
                ->setNumero($data->getNumero())
                ->setInfo($data->getInfo())
        ;
        
        if($candidato->getId()) {
            $candidato->setEdicao(new DateTime())->setEditor($this->security->getUser());
        } else {
            $candidato->setCriacao(new DateTime())->setCriador($this->security->getUser());
        }
        
        return $this->_save($candidato);
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
    
    public function addVoto(Candidatos $candidato) {
        $candidato->setVotosQtd((int)$candidato->getVotosQtd() + 1);
        $this->_save($candidato);
    }
    
    public function removeVoto(Candidatos $candidato) {
        $candidato->setVotosQtd((int)$candidato->getVotosQtd() - 1);
        $this->_save($candidato);
    }
    
    /****************************************************************************************   / REGRA DE NEGÓCIO   ***/
    
}


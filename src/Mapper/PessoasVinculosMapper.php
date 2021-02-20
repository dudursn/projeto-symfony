<?php

namespace App\Mapper;

use App\Entity\PessoasVinculos;
use App\Mapper\AbstractMapper;
use App\Services\Constants;
use App\Services\FilesService;
use App\Services\TextService;
use DateTime;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PessoasVinculosMapper extends AbstractMapper
{
    // 'id', 'criacao', 'criador', 'criador_colaborador', 'edicao', 'editor', 'editor_colaborador', 'pessoa1', 'pessoa1_vinculo', 'pessoa1_vinculo_outro', 'pessoa2', 'pessoa2_vinculo', 'pessoa2_vinculo_outro', 'ativo'
    protected $entityClass = PessoasVinculos::class;
    protected $selectFrom = 'pv';
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($pessoaVinculo, $data) { // PessoasVinculos 
        $this->checkEntityClass($pessoaVinculo);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$data->getPessoa1()) {
            $errors[] = 'Pessoa 1 não identificada';
        }
        if($data->getPessoa1Vinculo()) {
            if($data->getPessoa1Vinculo() == 'outro' && !$data->getPessoa1VinculoOutro())
                $errors[] = 'Por favor, informe o outro tipo de relação de ' . $data->getPessoa1()->getNome();
            if($data->getPessoa1Vinculo() != 'outro')
                $data->setPessoa1VinculoOutro(null);
        }
        if(!$data->getPessoa2()) {
            $errors[] = 'Pessoa 2 não identificada';
        }
        if($data->getPessoa2Vinculo()) {
            if($data->getPessoa2Vinculo() == 'outro' && !$data->getPessoa2VinculoOutro())
                $errors[] = 'Por favor, informe o outro tipo de relação de ' . $data->getPessoa2()->getNome();
            if($data->getPessoa2Vinculo() != 'outro')
                $data->setPessoa2VinculoOutro(null);
        }
        
        if($data->getPessoa1() == $data->getPessoa2()) {
            $errors[] = 'Não é possível criar Vínculos com a Mesma Pessoa';
            $errors[] = 'Por favor, cancele e selecione outra Pessoa';
        }
        
        if(count($this->select(['pessoa1'=>$data->getPessoa1(), 'pessoa2'=>$data->getPessoa2(), 'id!='=>$data->getId()])))
            $errors[] = 'Um Vínculo entre essas duas Pessoas já foi adicionado. Por favor, cancele e selecione outras Pessoas';
        if(count($this->select(['pessoa1'=>$data->getPessoa2(), 'pessoa2'=>$data->getPessoa1(), 'id!='=>$data->getId()])))
            $errors[] = 'Um Vínculo entre essas duas Pessoas já foi adicionado. Por favor, cancele e selecione outras Pessoas';
        
        if(count($errors)) {            
            return $errors;
        }
        
        $pessoaVinculo->setPessoa1Vinculo($data->getPessoa1Vinculo())
                ->setPessoa1VinculoOutro($data->getPessoa1VinculoOutro())
                ->setPessoa2Vinculo($data->getPessoa2Vinculo())
                ->setPessoa2VinculoOutro($data->getPessoa2VinculoOutro())
                ->setAtivo($data->getAtivo())
                ;
        
        if($pessoaVinculo->getId()) {
            $pessoaVinculo->setEdicao(new DateTime())
                    ->setEditor($this->security->getUser())
                    ->setEditorColaborador($this->session->get('colaborador', null));
        } else {
            $pessoaVinculo->setCriacao(new DateTime())
                    ->setCriador($this->security->getUser())
                    ->setCriadorColaborador($this->session->get('colaborador', null));
        }
        
        return $this->_save($pessoaVinculo);
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
            } else if ($atrib == 'pessoa1') {
                if($valor instanceof \App\Entity\Pessoas)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.pessoa1 = :pessoa1')->setParameter('pessoa1', (int)$valor);
            } else if ($atrib == 'pessoa2') {
                if($valor instanceof \App\Entity\Pessoas)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.pessoa2 = :pessoa2')->setParameter('pessoa2', (int)$valor);
            } else if ($atrib == 'pessoa1_pessoa2') {
                if($valor instanceof \App\Entity\Pessoas)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.pessoa1 = :pessoa1_pessoa2 or ' . $from . '.pessoa2 = :pessoa1_pessoa2')->setParameter('pessoa1_pessoa2', (int)$valor);
            } else if ($atrib == 'pessoa1_pessoa2_vinculo') {
                if ($valor != '')
                    $query->andWhere($from . '.pessoa1Vinculo = :pessoa1_pessoa2_vinculo or ' . $from . '.pessoa2Vinculo = :pessoa1_pessoa2_vinculo')->setParameter('pessoa1_pessoa2_vinculo', $valor);
            } else if ($atrib == 'ativo') {
                if (is_numeric($valor))
                    $query->andWhere($from . '.ativo = :ativo')->setParameter('ativo', $valor);
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


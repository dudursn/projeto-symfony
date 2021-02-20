<?php

namespace App\Mapper;

use App\Entity\Cidades;
use App\Entity\Eleicoes;
use App\Entity\Lojas;
use App\Entity\Ufs;
use App\Mapper\AbstractMapper;
use App\Services\FilesService;
use App\Services\TextService;
use DateTime;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class EleicoesMapper extends AbstractMapper
{
    // 'id', 'criacao', 'criador', 'criador_colaborador', 'edicao', 'editor', 'editor_colaborador', 'loja', 'nome', 'tipo', 'doc', 'role', 'identificacao', 'cargo', 'rg', 'matricula', 'nascimento', 'sexo', 'nit', 'titulo_eleitor', 'estado_civil', 'ctps', 'mae_nome', 'pai_nome', 'razao_social', 'insc_esta', 'insc_muni', 'genero', 'contato_nome', 'contato_cargo', 'contato_email', 'contato_tel', 'dados_bancarios', 'logradouro', 'enumero', 'complemento', 'bairro', 'uf', 'cidade', 'cep', 'tel1', 'tel1_obs', 'tel2', 'tel2_obs', 'tel3', 'tel3_obs', 'tel4', 'tel4_obs', 'email', 'obs', 'imagem', 'comissao_valor', 'comissao_percentagem'
    protected $entityClass = Eleicoes::class;
    protected $selectFrom = 'a';
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($eleicao, $data) { // Eleicoes 
        $this->checkEntityClass($eleicao);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$data->getAno())
            $errors[] = 'O campo Ano não pode estar vazio';
        if(!$data->getDescricao())
            $errors[] = 'O campo Descrição não pode estar vazio';
        
        if(count($this->select(['ano='=>$data->getAno(), 'id!='=>$eleicao->getId()])))
            $errors[] = 'Uma Eleição com o mesmo Ano já foi cadastrada. Por favor, informe outro valor para o campo Ano';
        
        if(count($errors)) {
            return $errors;
        }
        
        $eleicao->setAno($data->getAno())
                ->setDescricao($data->getDescricao())
                ->setVotacaoInicio($data->getVotacaoInicio())
                ->setVotacaoFim($data->getVotacaoFim())
                ->setApuracaoData($data->getApuracaoData())
                ->setAtivo($data->getAtivo())
                ;
        
        if($eleicao->getId()) {
            $eleicao->setEdicao(new DateTime())->setEditor($this->security->getUser());
        } else {
            $eleicao->setCriacao(new DateTime())->setCriador($this->security->getUser());
        }
        
        return $this->_save($eleicao);
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
                if($valor instanceof Lojas)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.loja = :loja')->setParameter('loja', (int)$valor);
            } else if ($atrib == 'uf') {
                if($valor instanceof Ufs)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.uf = :uf')->setParameter('uf', (int)$valor);
            } else if ($atrib == 'cidade') {
                if($valor instanceof Cidades)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.cidade = :cidade')->setParameter('cidade', (int)$valor);
            } else if ($atrib == 'ano=') {
                if ($valor != '')
                    $query->andWhere($from . '.ano = :ano')->setParameter('ano', $valor);
            } else if ($atrib == 'nome_identificacao') {
                if ($valor != '')
                    $query->andWhere($from . '.nome like :nome_identificacao or ' . $from . '.identificacao like :nome_identificacao')->setParameter('nome_identificacao', TextService::spaceToPercent($valor));
            } else if ($atrib == 'doc=') {
                if ($valor != '')
                    $query->andWhere($from . '.doc = :doc')->setParameter('doc', $valor);
            } else if ($atrib == 'ativo') {
                if (is_numeric($valor) || is_bool($valor))
                    $query->andWhere($from . '.ativo = :ativo')->setParameter('ativo', $valor);
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
    
    public function eleicaoAtual() {
        $eleicoes = $this->select(['ativo' => true, 'limit' => 1]);
        if(count($eleicoes))
            return $eleicoes[0];
        return null;
    }

    public function addVoto(Eleicoes $eleicao) {
        $eleicao->setVotosQtd((int)$eleicao->getVotosQtd() + 1);
        $this->_save($eleicao);
    }
    
    public function removeVoto(Eleicoes $eleicao) {
        $eleicao->setVotosQtd((int)$eleicao->getVotosQtd() - 1);
        $this->_save($eleicao);
    }
    
    /****************************************************************************************   / REGRA DE NEGÓCIO   ***/
    
}


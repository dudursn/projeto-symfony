<?php

namespace App\Mapper;

use App\Entity\Categorias;
use App\Mapper\AbstractMapper;
use App\Services\Constants;
use DateTime;
use App\Services\TextService;

class CategoriasMapper extends AbstractMapper
{
    //'id', 'criacao', 'criador', 'criador_colaborador', 'edicao', ' editor', 'editor_colaborador', 'loja', 'categoria', 'nome', 'ativo', 'ordem', 'contador', 'itens', 'pedidos', 'pagamentos', 'compras', 'despesas', 'tarefas'
    protected $entityClass = Categorias::class;
    protected $selectFrom = 'c';
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($categoria, $data) { // Categorias 
        $this->checkEntityClass($categoria);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$categoria->getLoja())
            $errors[] = 'Loja não identificada';
        if(!$data->getNome())
            $errors[] = 'O campo Nome não pode estar vazio';
        if(!$data->getEscopo())
            $errors[] = 'O campo Tipo de Categoria não pode estar vazio';
        else if(!in_array($data->getEscopo(), Constants::categoriasEscopos()))
            $errors[] = 'Escopo da Categoria não identificado';
        if($data->getCategoria() && $data->getCategoria()->getId() == $categoria->getId())
            $errors[] = 'Uma Categoria não pode ser filha dela mesmo. Selecione outra Categoria Pai';
        if($data->getCategoria() && $data->getEscopo() != $data->getCategoria()->getEscopo())
            $errors[] = 'O Tipo de Categoria precisa ser igual ao Tipo de Categoria da Categoria Pai';
        
        if(count($this->select(['loja'=>$data->getLoja(), 'nome='=>$data->getNome(), 'escopo'=>$data->getEscopo(), 'categoria'=>$data->getCategoria(), 'categoria_is_null'=>$data->getCategoria()?0:1, 'id!='=>$data->getId()])))
            $errors[] = 'Uma Categoria com o mesmo NOME já foi cadastrado. Por favor, informe outro Nome para esta Categoria';
        
        if(count($errors)) {            
            return $errors;
        }
        
        $categoria->setNome($data->getNome())
                ->setCategoria($data->getCategoria())
                ->setEscopo($data->getEscopo())
                ->setAtivo($data->getAtivo())
                ->setOrdem($data->getOrdem())
                ;
        
        if($categoria->getId()) {
            $categoria->setEdicao(new DateTime())
                    ->setEditor($this->security->getUser())
                    ->setEditorColaborador($this->session->get('colaborador', null));
        } else {
            $categoria->setCriacao(new DateTime())
                    ->setCriador($this->security->getUser())
                    ->setCriadorColaborador($this->session->get('colaborador', null));
        }
        
        return $this->_save($categoria);
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
                if($valor instanceof \App\Entity\Lojas)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.loja = :loja')->setParameter('loja', (int)$valor);
            } else if ($atrib == 'categoria') {
                if($valor instanceof Categorias)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.categoria = :categoria')->setParameter('categoria', (int)$valor);
            } else if ($atrib == 'categoria_chain') {
                if($valor instanceof Categorias)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.categoria in (:categoria_chain)')->setParameter('categoria_chain', $this->getChainId($valor));
            } else if ($atrib == 'categoria_is_null') {
                if ($valor != 0)
                    $query->andWhere($from . '.categoria is null');
            } else if ($atrib == 'nome=') {
                if ($valor != '')
                    $query->andWhere($from . '.nome = :nome_equal')->setParameter('nome_equal', $valor);
            } else if ($atrib == 'escopo') {
                if ($valor == 'itens')
                    $query->andWhere($from . '.itens = :itens')->setParameter('itens', true);
                else if($valor == 'pessoas')
                    $query->andWhere($from . '.pessoas = :pessoas')->setParameter('pessoas', true);
                else if($valor == 'pedidos')
                    $query->andWhere($from . '.pedidos = :pedidos')->setParameter('pedidos', true);
                else if($valor == 'compras')
                    $query->andWhere($from . '.compras = :compras')->setParameter('compras', true);
                else if($valor == 'pagamentos')
                    $query->andWhere($from . '.pagamentos = :pagamentos')->setParameter('pagamentos', true);
                else if($valor == 'despesas')
                    $query->andWhere($from . '.despesas = :despesas')->setParameter('despesas', true);
                else if($valor == 'tarefas')
                    $query->andWhere($from . '.tarefas = :tarefas')->setParameter('tarefas', true);
            } else if ($atrib == 'ativo') {
                if (is_numeric($valor))
                    $query->andWhere($from . '.ativo = :ativo')->setParameter('ativo', (int)$valor);
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
    
    public function parents(Categorias $categoria, $returnLast = false)
    {
        $categoria->getNome();
        $parents = [$categoria];
        if($categoria->getCategoria())
            $parents = array_merge($this->parents($categoria->getCategoria(), true), $parents);
        if(!$returnLast)           
            array_pop($parents);
        return $parents;
    }
    
    public function parentsJson(Categorias $categoria, $returnLast = false)
    {
        $parents = [];
        foreach ($this->parents($categoria, $returnLast) as $parent)
            $parents[] = ['value' => $parent->getId(), 'label' => $parent->getNome()];
        return json_encode($parents);
    }
    
    public function getChainId($id) {
        if(!$id) return [];
        $chain = [$id];
        foreach ($this->select(['categoria' => $id]) as $item)
            $chain = array_merge($chain, $this->getChainId($item->getId()));
        return $chain;
    }
    
    /**************************************************************************************   / REGRA DE NEGÓCIO   ***/
    
}


<?php

namespace App\Mapper;

use App\Entity\PedidosItens;
use App\Mapper\AbstractMapper;
use App\Services\Constants;
use App\Services\DateService;
use App\Services\FilesService;
use App\Services\TextService;
use DateTime;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PedidosItensMapper extends AbstractMapper
{
    // 'id', 'criacao', 'criador', 'criador_colaborador', 'edicao', 'editor', 'editor_colaborador', 'pedido', 'item', 'numero', 'codigo', 'descricao', 'medida', 'qtd', 'valor', 'desconto', 'valor_total_sem_desconto', 'valor_total', 'sem_valor', 'sem_valor_justificativa', 'ajustar_estoque', 'ajustado_estoque'
    protected $entityClass = PedidosItens::class;
    protected $selectFrom = 'pi';
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($peditoItem, $data) { // PedidosItens 
        $this->checkEntityClass($peditoItem);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$peditoItem->getPedido() || !$peditoItem->getPedido()->getId()) {
            $errors[] = 'O Pedido deste Item não foi identificado';
        }
        
        if(!$data->getDescricao())
            $errors[] = 'Por favor, informe a Descrição do Item';
        if($data->getQtd() <= 0)
            $errors[] = 'O campo Quantidade não pode ser um número menor que 1. Por favor, informe uma quantidade';
        if($data->getValor() < 0)
            $errors[] = 'O campo Valor Unitário não pode ser um número negativo';
        if($data->getDesconto() < 0)
            $errors[] = 'O campo Desconto não pode ser um número negativo';
        $data->setValorTotalSemDesconto($data->getValor() * $data->getQtd());
        $data->setValorTotal($data->getValorTotalSemDesconto() - $data->getDesconto());
        if($data->getValorTotal() < 0)
            $errors[] = 'O campo Desconto não pode ser maior que o Valor do Item';
        if($data->getSemValor()) {
            if(!$data->getSemValorJustificativa())
                $errors[] = 'Por favor, informe um motivo para não cobrar pelo Item';
            $data->setDesconto(null)->setValorTotalSemDesconto(null)->setValorTotal(null);
        } else {
            $data->setSemValorJustificativa(null);
            if($data->getValor() === 0)
                $errors[] = 'O campo Valor Unitário não pode estar vazio';
        }
        
        if(count($errors)) {            
            return $errors;
        }
        
        $peditoItem->setDescricao($data->getDescricao())
                ->setCodigo($data->getCodigo())
                ->setMedida($data->getMedida())
                ->setQtd($data->getQtd())
                ->setValor($data->getValor())
                ->setDesconto($data->getDesconto())
                ->setValorTotalSemDesconto($data->getValorTotalSemDesconto())
                ->setValorTotal($data->getValorTotal())
                ->setSemValor($data->getSemValor())
                ->setSemValorJustificativa($data->getSemValorJustificativa())
                ->setAjustarEstoque($data->getAjustarEstoque())
                ->setItem($data->getItem())
                ;
        
        if($peditoItem->getId()) {
            $peditoItem->setEdicao(new DateTime())
                    ->setEditor($this->security->getUser())
                    ->setEditorColaborador($this->session->get('colaborador', null));
        } else {
            $peditoItem->setCriacao(new DateTime())
                    ->setCriador($this->security->getUser())
                    ->setCriadorColaborador($this->session->get('colaborador', null))
                    ->setAjustadoEstoque(false)
                    ->setNumero($this->proximoNumero($peditoItem->getPedido()));
        }
        
        return $this->_save($peditoItem);
    }

    public function select(array $atribs = array(), $orderBy = array(), $page = null, $join = array()) {
        $from = $this->selectFrom;
        $query = $this->repository->createQueryBuilder($from);
        /***********************************************************************************************   JOIN    ***/
        if(!is_array($join))
            $join = array($join);
        foreach ($join as $table) {
            if($table == 'pedidos') {
                $pedidos_from = 'p';
                $query->innerJoin($from . '.pedido', $pedidos_from)->addSelect($pedidos_from);
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
            } else if ($atrib == 'pedido') {
                if($valor instanceof \App\Entity\Pedidos)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.pedido = :pedido')->setParameter('pedido', (int)$valor);
            } else if ($atrib == 'item') {
                if($valor instanceof \App\Entity\Itens)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.item = :v')->setParameter('item', (int)$valor);
            } else if ($atrib == 'limit') {
                if (is_numeric($valor))
                    $query->setMaxResults($valor);
            } else if ($atrib == 'pedido_loja') {
                if($valor instanceof \App\Entity\Lojas)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($pedidos_from . '.loja = :pedido_loja')->setParameter('pedido_loja', (int)$valor);
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
    
    public function proximoNumero(\App\Entity\Pedidos $pedido) :?int
    {
        $erros = [];
        if(!$pedido || !$pedido->getId())
            return ['Pedido não detectado'];
        if(count($pedidosItens = $this->select(['pedido' => $pedido, 'limit' => 1], ['numero' => 'desc']))) {
            return $pedidosItens[0]->getNumero() + 1;
        }
        return 1;
    }
    
    public function reordenarNumeros(PedidosItens $pedidoItem, $pos = null)
    {
        $index = $pedidoItem->getNumero() - 1;
        $newIndex = $pos == 'subir'?$index - 1:($pos == 'descer'?$index + 1:(is_numeric($pos)?$pos - 1:-1));
        
        if($pedidoItem->getPedido()) {
            $i = 0;
            $itens = $pedidoItem->getPedido()->getPedidosItensByNumero();
            if($newIndex >= 0) {
                $currentRow = $itens[$index];
                $otherRow = $itens[$newIndex];
                $itens[$index] = $otherRow;
                $itens[$newIndex] = $currentRow;
            }
            foreach ($itens as $item) {
                $item->setNumero(++$i);
            }
            if($i)
                $this->_save($item);
        }
        return true;
    }
    
    /**************************************************************************************   / REGRA DE NEGÓCIO   ***/
    
}


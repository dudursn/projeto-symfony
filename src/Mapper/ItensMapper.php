<?php

namespace App\Mapper;

use App\Entity\Itens;
use App\Mapper\AbstractMapper;
use App\Services\Constants;
use DateTime;
use App\Services\TextService;

class ItensMapper extends AbstractMapper
{
    // 'id', 'criacao', 'criador', 'criador_colaborador', 'edicao datetime, editor', 'editor_colaborador', 'loja', 'categoria', 'descricao', 'codigo', 'medida', 'valor_venda', 'valor_custo_fonte', 'valor_custo', 'valor_custo_compra_item', 'qtd', 'qtd_min', 'qtd_vendidos', 'qtd_vendidos_inicial', 'qtd_vendidos_total'
    // 'nf_cfop', 'nf_ncm', 'nf_gtin', 'nf_gtin_tributavel', 'nf_cest', 'nf_icms_csosn', 'nf_icms_origem_cstb', 'nf_icms_origem', 'nf_icms_aliquota_calc_credito', 'nf_icms_valor_credito', 'nf_icms_mod_base_calculo', 'nf_icms_base_calculo', 'nf_icms_reducao_base_calculo', 'nf_icms_aliquota', 'nf_icms_valor', 'nf_icms_mod_bc_st', 'nf_icms_bc_st', 'nf_icms_aliquota_st', 'nf_icms_valor_st', 'nf_icms_red_bc_st', 'nf_icms_valor_bc_st_ret', 'nf_icms_valor_st_ret', 'nf_icms_mva_st', 'nf_icms_valor_bc_uf_destino', 'nf_icms_aliquota_uf_destino', 'nf_icms_valor_int_uf_destino', 'nf_icms_aliquota_int_uf_envolvidas', 'nf_icms_valor_int_uf_remetente', 'nf_icms_aliquota_perc_part_int', 'nf_icms_aliquota_fundo_combate_pobreza_uf_dest', 'nf_icms_valor_fundo_combate_pobreza_uf_dest', 'nf_pis_situacao_tributaria', 'nf_pis_base_calculo', 'nf_pis_aliquota', 'nf_pis_valor', 'nf_cofins_situacao_tributaria', 'nf_cofins_base_calculo', 'nf_cofins_aliquota', 'nf_cofins_valor', 'nf_ipi_situacao_tributaria', 'nf_ipi_base_calculo', 'nf_ipi_aliquota', 'nf_ipi_valor', 'nf_ipi_classe_enquadramento', 'nf_ipi_codigo_enquadramento_legal', 'nf_ipi_cnpj_prod', 'nf_ipi_codigo_selo', 'nf_ipi_qtde_selo', 'nf_ipi_qtde_unidade', 'nf_ii_base_calculo', 'nf_ii_iof_aliquota', 'nf_ii_valor', 'nf_ii_aliquota', 'nf_ii_iof_valor', 'nf_ii_valor_despesas_aduaneiras', 'nf_iss_data_prestacao_servico', 'nf_iss_valor_bc', 'nf_iss_valor_aliquota', 'nf_iss_valor', 'nf_iss_cod_cidade', 'nf_iss_cod_item_lc', 'nf_iss_valor_deducao', 'nf_iss_valor_outras_retencoes', 'nf_iss_valor_desc_incond', 'nf_iss_valor_desc_cond', 'nf_iss_valor_total_retido', 'nf_iss_indicador', 'nf_iss_cod_servico', 'nf_iss_cod_municipio', 'nf_iss_cod_pais', 'nf_iss_cod_processo', 'nf_iss_incentivo', 'nf_iss_codigo_regime_trib'
    protected $entityClass = Itens::class;
    protected $selectFrom = 'i';
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($item, $data) { // Itens 
        $this->checkEntityClass($item);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$item->getLoja())
            $errors[] = 'Loja não identificada';
        if(!$data->getDescricao())
            $errors[] = 'O campo Descrição não pode estar vazio';
        else
            $data->setDescricao(str_replace(["\r", "\n"], '', $data->getDescricao()));
        if(!$data->getValorVenda())
            $errors[] = 'O campo Valor de Venda não pode estar vazio';
        
        if(count($this->select(['loja'=>$data->getLoja(), 'descricao='=>$data->getDescricao(), 'codigo='=>$data->getCodigo(), 'id!='=>$data->getId()])))
            $errors[] = 'Um Item com a mesma Descrição já foi cadastrado. Por favor, informe outra Descrição para este Item';
        
        if(count($errors)) {            
            return $errors;
        }
        
        if(!$data->view('valor_custo_fonte_manual?'))
            $data->setValorCusto(null);
        if(!$data->getQtdMin())
            $data->setQtdMin(0);
        
        $item->setCategoria($data->getCategoria())
                ->setDescricao($data->getDescricao())
                ->setCodigo($data->getCodigo())
                ->setMedida($data->getMedida())
                ->setValorVenda($data->getValorVenda())
                ->setValorCustoFonte($data->getValorCustoFonte())
                ->setValorCusto($data->getValorCusto())
                ->setQtdMin($data->getQtdMin())
                ->setQtdIgnorar($data->getQtdIgnorar())
                ;
        
        if($item->getId()) {
            $item->setEdicao(new DateTime())
                    ->setEditor($this->security->getUser())
                    ->setEditorColaborador($this->session->get('colaborador', null));
        } else {
            $item->setCriacao(new DateTime())
                    ->setCriador($this->security->getUser())
                    ->setCriadorColaborador($this->session->get('colaborador', null))
                    ->setQtdVendidosInicial(new DateTime());
        }
        
        return $this->_save($item);
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
                if($valor instanceof \App\Entity\Categorias)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.categoria = :categoria')->setParameter('categoria', (int)$valor);
            } else if ($atrib == 'categoria_chain') {
                if($valor instanceof \App\Entity\Categorias)
                    $query->andWhere($from . '.categoria in (:categoria_chain)')->setParameter('categoria_chain', $valor->view('chain_id'));
            } else if ($atrib == 'categoria_is_null') {
                if ($valor != 0)
                    $query->andWhere($from . '.categoria is null');
            } else if ($atrib == 'descricao=') {
                if ($valor != '')
                    $query->andWhere($from . '.descricao = :descricao_equal')->setParameter('descricao_equal', $valor);
            } else if ($atrib == 'codigo=') {
                if ($valor != '')
                    $query->andWhere($from . '.codigo = :codigo_equal')->setParameter('codigo_equal', $valor);
            } else if ($atrib == 'codigo_descricao') {
                if ($valor != '')
                    $query->andWhere($from . '.codigo like :codigo_descricao or ' . $from . '.descricao like :codigo_descricao')->setParameter('codigo_descricao', TextService::spaceToPercent($valor));
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
    
    public function saveNf(Itens $item) {
        $this->checkEntityClass($item, true, true);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(count($errors)) {            
            return $errors;
        }
        
        $item->setEdicao(new DateTime())
                ->setEditor($this->security->getUser())
                ->setEditorColaborador($this->session->get('colaborador', null));
        
        return $this->_save($item);
    }

    public function saveQuantidade(Itens $item, $novoTipo, $novaQuantidade, $tipoInicial = null, $quantidadeInicial = null) {
        $this->checkEntityClass($item, true, true);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        $qtd = $item->getQtd();
        if($tipoInicial && $quantidadeInicial) {
            if($tipoInicial === 1)
                $qtd = $qtd - $quantidadeInicial;
            else if($tipoInicial === 2)
                $qtd = $qtd + $quantidadeInicial;
        }
        if($novoTipo === 1)
            $qtd = $qtd + $novaQuantidade;
        else if($novoTipo === 2)
            $qtd = $qtd - $novaQuantidade;
        if(count($errors)) {            
            return $errors;
        }
        $item->setQtd($qtd);
        return $this->_save($item);
    }

    public function ajustarEstoquePedido(\App\Entity\PedidosItens $removePedidoItem = null, \App\Entity\PedidosItens $addPedidoItem = null)
    {
        if(!$addPedidoItem && !$removePedidoItem)
            die('ItensMapper::ajustarEstoquePedido: Não foi possível identificar o nenhum Pedido Item');
        
        if($removePedidoItem && $removePedidoItem->getId() && ($item = $this->find($removePedidoItem->getItem())) && !$item->getQtdIgnorar()) {
            if($removePedidoItem->getAjustarEstoque())
                $item->setQtd($item->getQtd() - $removePedidoItem->getQtd());
            $item->setQtdVendidos($item->getQtdVendidos() + $removePedidoItem->getQtd());
            $item->setQtdVendidosTotal($item->getQtdVendidosTotal() + $removePedidoItem->getQtd());
            $this->_save($item);
        }
        
        if($addPedidoItem && ($item = $this->find($addPedidoItem->getItem())) && !$item->getQtdIgnorar()) {
            if($addPedidoItem->getAjustarEstoque())
                $item->setQtd($item->getQtd() + $addPedidoItem->getQtd());
            $item->setQtdVendidos($item->getQtdVendidos() - $addPedidoItem->getQtd());
            $item->setQtdVendidosTotal($item->getQtdVendidosTotal() - $addPedidoItem->getQtd());
            $this->_save($item);
        }
    }
    
    /**************************************************************************************   / REGRA DE NEGÓCIO   ***/
    
}


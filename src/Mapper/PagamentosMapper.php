<?php

namespace App\Mapper;

use App\Entity\Fundos;
use App\Entity\Pagamentos;
use App\Entity\Pedidos;
use App\Mapper\AbstractMapper;
use App\Services\Constants;
use App\Services\DateService;
use App\Services\TextService;
use DateTime;

class PagamentosMapper extends AbstractMapper
{
    // 'id', 'criacao', 'criador', 'criador_colaborador', 'edicao', 'editor', 'editor_colaborador', 'loja', 'caixa', 'pessoa', 'pedido', 'forma', 'numero', 'vencimento', 'valor', 'desconto', 'valor_total', 'descricao', 'categoria', 'pagamento_datahora', 'pagamento_login', 'pagamento_colaborador', 'pagamento_data', 'pagamento_valor', 'pagamento_acrescimos', 'fundo', 'desconto_vencimento', 'desconto_vencimento_percentual', 'multa', 'juros', 'situacao', 'cancelado_datahora', 'cancelado_login', 'cancelado_colaborador', 'cancelado_justificativa', 'obs', 'ignorar_cobranca', 'ignorar_cobranca_datahora', 'ignorar_cobranca_login', 'ignorar_cobranca_colaborador', 'cartao_bandeira', 'cartao_modo', 'cartao_parcelas', ' cartao_operadora_codigo', 'comissionado', 'comissionado_quitado', 'comissionado_quitado_datahora', 'comissionado_quitado_login', 'comissionado_quitado_colaborador', 'online_operadora', 'online_id', 'online_situacao'
    protected $entityClass = Pagamentos::class;
    protected $selectFrom = 'p';
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($pagamento, $data) { // Pagamentos 
        $this->checkEntityClass($pagamento);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$pagamento->getLoja())
            $errors[] = 'Loja não identificada';
        
        if($data->getPedido()) {
            $data->setPessoa($data->getPedido()->getPessoa());
        } else {
            $data->setPessoa($data->getPessoa()?$data->getPessoa():$pagamento->getPessoa());
        }
        
        if($pagamento->getPedido() && $pagamento->getPedido()->getCortesia())
            $errors[] = 'Não é possível adicionar Pagamentos a um Pedido marcado com CORTESIA';
        
        if(!$data->getPedido() && !$data->getDescricao())
            $errors[] = 'Por favor, informe uma Descrição para idenfiticar este Pagamento';
        
        if($data->getValor() < 0)
            $errors[] = 'O campo Valor Unitário não pode ser um número negativo';
        if($data->getDesconto() < 0)
            $errors[] = 'O campo Desconto não pode ser um número negativo';
        if($data->getPagamentoAcrescimos() < 0)
            $errors[] = 'O campo Acréscimos não pode ser um número negativo';
        $data->setValorTotal($data->getValor() - $data->getDesconto() + $data->getPagamentoAcrescimos());
        if($data->getValorTotal() < 0)
            $errors[] = 'O campo Desconto não pode ser maior que o Valor do Pagamento';
        
        if(!$data->view('forma_cartao?')) {
            $data->setCartaoBandeira(null)
                    ->setCartaoModo(null)
                    ->setCartaoParcelas(null)
                    ->setCartaoOperadoraCodigo(null);
        }
        
        if(count($errors)) {            
            return $errors;
        }
        
        $pagamento->setPessoa($data->getPessoa())
                ->setPedido($data->getPedido())
                ->setForma($data->getForma())
                ->setVencimento($data->getVencimento())
                ->setValor($data->getValor())
                ->setDesconto($data->getDesconto())
                ->setValorTotal($data->getValorTotal())
                ->setDescricao($data->getDescricao())
                ->setCategoria($data->getCategoria())
                ->setDescontoVencimento($data->getDescontoVencimento())
                ->setDescontoVencimentoPercentual($data->getDescontoVencimentoPercentual())
                ->setMulta($data->getMulta())
                ->setJuros($data->getJuros())
                ->setObs($data->getObs())
                ->setCartaoBandeira($data->getCartaoBandeira())
                ->setCartaoModo($data->getCartaoModo())
                ->setCartaoParcelas($data->getCartaoParcelas())
                ->setCartaoOperadoraCodigo($data->getCartaoOperadoraCodigo())
                ->setComissionado($data->getComissionado())
                ;
        
        if($pagamento->getId()) {
            $pagamento->setEdicao(new DateTime())
                    ->setEditor($this->security->getUser())
                    ->setEditorColaborador($this->session->get('colaborador', null));
        } else {
            $pagamento->setCriacao(new DateTime())
                    ->setCriador($this->security->getUser())
                    ->setCriadorColaborador($this->session->get('colaborador', null))
                    ->setSituacao(0)
                    ;
            if($pagamento->getPedido())
                $pagamento->setNumero($this->proximoNumero($pagamento->getPedido()));
        }
        
        return $this->_save($pagamento);
    }

    /*
    public function save($pagamento, $data) { // Pagamentos 
        $this->checkEntityClass($pagamento);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$pagamento->getLoja())
            $errors[] = 'Loja não identificada';
        
        if($data->getPedido()) {
            $data->setPessoa($data->getPedido()->getPessoa());
        } else {
            $data->setPessoa($data->getPessoa()?$data->getPessoa():$pagamento->getPessoa());
        }
        
        if($pagamento->getPedido() && $pagamento->getPedido()->getCortesia())
            $errors[] = 'Não é possível adicionar Pagamentos a um Pedido marcado com CORTESIA';
        
        if($data->getValor() < 0)
            $errors[] = 'O campo Valor Unitário não pode ser um número negativo';
        if($data->getDesconto() < 0)
            $errors[] = 'O campo Desconto não pode ser um número negativo';
        $data->setValorTotal($data->getValor() - $data->getDesconto());
        if($data->getValorTotal() < 0)
            $errors[] = 'O campo Desconto não pode ser maior que o Valor do Pagamento';
        
        if(!$data->getPedido()) {
            if(!$data->getDescricao())
                $errors[] = 'Por favor, informe uma Descrição para idenfiticar este Pagamento';
        }
        
        if(!$data->view('forma_cartao?')) {
            $data->setCartaoBandeira(null)
                    ->setCartaoModo(null)
                    ->setCartaoParcelas(null)
                    ->setCartaoOperadoraCodigo(null);
        }
        
        if(count($errors)) {            
            return $errors;
        }
        
        $pagamento->setPessoa($data->getPessoa())
                ->setPedido($data->getPedido())
                ->setForma($data->getForma())
                ->setVencimento($data->getVencimento())
                ->setValor($data->getValor())
                ->setDesconto($data->getDesconto())
                ->setValorTotal($data->getValorTotal())
                ->setDescricao($data->getDescricao())
                ->setCategoria($data->getCategoria())
                ->setDescontoVencimento($data->getDescontoVencimento())
                ->setDescontoVencimentoPercentual($data->getDescontoVencimentoPercentual())
                ->setMulta($data->getMulta())
                ->setJuros($data->getJuros())
                ->setObs($data->getObs())
                ->setCartaoBandeira($data->getCartaoBandeira())
                ->setCartaoModo($data->getCartaoModo())
                ->setCartaoParcelas($data->getCartaoParcelas())
                ->setComissionado($data->getComissionado())
                ;
        
        if($pagamento->getId()) {
            $pagamento->setEdicao(new DateTime())
                    ->setEditor($this->security->getUser())
                    ->setEditorColaborador($this->session->get('colaborador', null));
        } else {
            $pagamento->setCriacao(new DateTime())
                    ->setCriador($this->security->getUser())
                    ->setCriadorColaborador($this->session->get('colaborador', null))
                    ->setSituacao(0)
                    ;
            if($pagamento->getPedido())
                $pagamento->setNumero($this->proximoNumero($pagamento->getPedido()));
        }
        
        return $this->_save($pagamento);
    }

    */
    
    public function select(array $atribs = array(), $orderBy = array(), $page = null, $join = array()) {
     
        $from = $this->selectFrom;
        $query = $this->repository->createQueryBuilder($from);
        /***********************************************************************************************   JOIN    ***/
        if(!is_array($join))
            $join = array($join);
        foreach ($join as $table) {
            if($table == 'pedidos') {
                $pedidos_from = 'pe';
                $query->leftJoin($from . '.pedido', $pedidos_from)->addSelect($pedidos_from);
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
            } else if ($atrib == 'pessoa') {
                if($valor instanceof \App\Entity\Pessoas)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.pessoa = :pessoa')->setParameter('pessoa', (int)$valor);
            } else if ($atrib == 'pedido') {
                if($valor instanceof Pedidos)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.pedido = :pedido')->setParameter('pedido', (int)$valor);
            } else if ($atrib == 'fundo') {
                if($valor instanceof Fundos)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.fundo = :fundo')->setParameter('fundo', (int)$valor);
            } else if ($atrib == 'vencimento') {
                if($valor instanceof DateTime)
                    $query->andWhere($from . '.vencimento = :vencimento')->setParameter('vencimento', $valor);
                else if ($valor != '')
                    $query->andWhere($from . '.vencimento = :vencimento')->setParameter('vencimento', DateService::converte($valor, 'en'));
            } else if ($atrib == 'vencimento_de') {
                if($valor instanceof DateTime)
                    $query->andWhere($from . '.vencimento >= :vencimento_de')->setParameter('vencimento_de', $valor);
                else if ($valor != '')
                    $query->andWhere($from . '.vencimento >= :vencimento_de')->setParameter('vencimento_de', DateService::converte($valor, 'en'));
            } else if ($atrib == 'vencimento_ate') {
                if($valor instanceof DateTime)
                    $query->andWhere($from . '.vencimento <= :vencimento_ate')->setParameter('vencimento_ate', $valor);
                else if ($valor != '')
                    $query->andWhere($from . '.vencimento <= :vencimento_ate')->setParameter('vencimento_ate', DateService::converte($valor, 'en'));
            } else if ($atrib == 'pagamento_data') {
                if($valor instanceof DateTime)
                    $query->andWhere($from . '.pagamentoData = :pagamentoData')->setParameter('pagamentoData', $valor);
                else if ($valor != '')
                    $query->andWhere($from . '.pagamentoData = :pagamentoData')->setParameter('pagamentoData', DateService::converte($valor, 'en'));
            } else if ($atrib == 'pagamento_data_de') {
                if($valor instanceof DateTime)
                    $query->andWhere($from . '.pagamentoData >= :pagamentoData_de')->setParameter('pagamentoData_de', $valor);
                else if ($valor != '')
                    $query->andWhere($from . '.pagamentoData >= :pagamentoData_de')->setParameter('pagamentoData_de', DateService::converte($valor, 'en'));
            } else if ($atrib == 'pagamento_data_ate') {
                if($valor instanceof DateTime)
                    $query->andWhere($from . '.pagamentoData <= :pagamentoData_ate')->setParameter('pagamentoData_ate', $valor);
                else if ($valor != '')
                    $query->andWhere($from . '.pagamentoData <= :pagamentoData_ate')->setParameter('pagamentoData_ate', DateService::converte($valor, 'en'));
            } else if ($atrib == 'situacao') {
                if ($valor != 0)
                    $query->andWhere($from . '.situacao = :situacao')->setParameter('situacao', (int)$valor);
            } else if ($atrib == 'forma') {
                if ($valor != '')
                    $query->andWhere($from . '.forma = :forma')->setParameter('forma', $valor);
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
            } else if ($atrib == 'comissionado') {
                if($valor instanceof \App\Entity\Colaboradores)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.comissionado = :comissionado')->setParameter('comissionado', (int)$valor);
            } else if ($atrib == 'limit') {
                if (is_numeric($valor))
                    $query->setMaxResults($valor);
            } else if ($atrib == 'pedido_numero') {
                if ($valor != '')
                    $query->andWhere($pedidos_from . '.numero like :pedido_numero')->setParameter('pedido_numero', TextService::spaceToPercent($valor));
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
    
    public function proximoNumero(Pedidos $pedido) :?int
    {
        $erros = [];
        if(!$pedido || !$pedido->getId())
            return null;
        if(count($pagamentos = $this->select(['pedido' => $pedido, 'limit' => 1], ['numero' => 'desc']))) {
            return $pagamentos[0]->getNumero() + 1;
        }
        return 1;
    }
    
    public function reordenarNumeros(Pagamentos $pagamento, $pos = null)
    {
        // Inútil... a ordem é pelo vencimento...
        $index = $pagamento->getNumero() - 1;
        $newIndex = $pos == 'subir'?$index - 1:($pos == 'descer'?$index + 1:(is_numeric($pos)?$pos - 1:-1));
        
        if($pagamento->getPedido()) {
            $i = 0;
            $itens = $pagamento->getPedido()->getPagamentosByNumero();
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
    
    public function saveQuitado(Pagamentos $pagamento, Pagamentos $data, PedidosMapper $pedidosMapper) { 
        $this->checkEntityClass($pagamento);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        $oldPagamento = clone $pagamento;
        $oldPagamento->setPagamentoValor(0);
        
        if($data->view('forma_cobranca_online?') || $data->view('forma_boleto_convenio?'))
            $errors[] = 'Forma de pagamento indisponível';
        
        if($data->getPagamentoValor() <= 0)
            $errors[] = 'Por favor, informe o Valor Pago';
        
        if(!$data->getPagamentoData())
            $errors[] = 'Por favor, informe uma Data de Pagamento';
        
        if(!$pagamento->getId()) {
            $data->setVencimento($data->getPagamentoData());
            if(!$data->getValor())
                $data->setValor($data->getPagamentoValor());
        }
        
        $data->setValorTotal($data->getValor() - $data->getDesconto() + $data->getPagamentoAcrescimos());
        
        if($data->getValorTotal() != $data->getPagamentoValor())
            $errors[] = 'O Valor Pago não pode ser diferente do Valor a Pagar';
        
        if(!$data->getFundo() && ($data->view('forma_dinheiro?') || $data->view('forma_transferencia?') || $data->view('forma_cheque?')))
            $errors[] = 'A Seleção de um Fundo de Dinheiro é obrigatório para a Forma de Pagamento selecionada';
        
        if(count($errors)) {            
            return $errors;
        }
        
        if(!$pagamento->getId()) {
            if(!(($pagamento = $this->save($pagamento, $data)) instanceof Pagamentos)) {
                return $pagamento;
            } 
        }
        
        $pagamento->setPagamentoData($data->getPagamentoData())
                ->setValor($data->getValor())
                ->setDesconto($data->getDesconto())
                ->setValorTotal($data->getValorTotal())
                ->setPagamentoAcrescimos($data->getPagamentoAcrescimos())
                ->setPagamentoValor($data->getPagamentoValor())
                ->setForma($data->getForma())
                ->setCartaoBandeira($data->getCartaoBandeira())
                ->setCartaoModo($data->getCartaoModo())
                ->setCartaoParcelas($data->getCartaoParcelas())
                ->setCartaoOperadoraCodigo($data->getCartaoOperadoraCodigo())
                ->setFundo($data->getFundo())
                ->setPagamentoDatahora(new DateTime())
                ->setPagamentoLogin($this->security->getUser())
                ->setPagamentoColaborador($this->session->get('colaborador', null))
                ->setSituacao(1)
                ;
        
        if($pagamento->getPedido())
            $pedidosMapper->ajustarValoresAddPagamento($pagamento->getPedido(), $pagamento, $oldPagamento, 'quitar');
        
        return $this->_save($pagamento);
    }

    public function cancelarQuitacao(Pagamentos $pagamento, PedidosMapper $pedidosMapper) { 
        $this->checkEntityClass($pagamento);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$pagamento->view('situacao_quitado?'))
            $errors[] = 'Pagamento não está quitado';
        
        if(count($errors)) {            
            return $errors;
        }
        
        $oldPagamento = clone $pagamento;
        
        $pagamento->setPagamentoDatahora(null)
                ->setPagamentoLogin(null)
                ->setPagamentoColaborador(null)
                ->setPagamentoData(null)
                ->setPagamentoValor(0)
                ->setPagamentoAcrescimos(0)
                ->setFundo(null)
                ->setSituacao(0)
                ;
        $pagamento->setValorTotal($pagamento->getValor() + $pagamento->getPagamentoAcrescimos() - $pagamento->getDesconto());
        
        if($pagamento->getPedido())
            $pedidosMapper->ajustarValoresAddPagamento($pagamento->getPedido(), $pagamento, $oldPagamento, 'quitar-cancelar');
        
        return $this->_save($pagamento);
    }

    public function cancelar(Pagamentos $pagamento, $data = [], $reverterCancelamento = false) { 
        $this->checkEntityClass($pagamento);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$reverterCancelamento && !$data['justificativa'])
            $errors[] = 'Por favor, informe uma Justificativa para cancelar este Pagamento';
        
        if(count($errors)) {
            return $errors;
        }
        
        $pagamento->setSituacao($reverterCancelamento?0:2)
                ->setCanceladoDatahora($reverterCancelamento?null:new DateTime())
                ->setCanceladoLogin($reverterCancelamento?null:$this->security->getUser())
                ->setCanceladoColaborador($reverterCancelamento?null:$this->session->get('colaborador', null))
                ->setCanceladoJustificativa($reverterCancelamento?null:$data['justificativa'])
                ;
        
        return $this->_save($pagamento);
    }

    public function saveParcelas(Pagamentos $pagamento, $data, PedidosMapper $pedidosMapper) {
        $this->checkEntityClass($pagamento);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        $qtdPagamentos = isset($data['pagamentos_qtd'])?(int)$data['pagamentos_qtd']:0;
        $ciclo = isset($data['ciclo'])?$data['ciclo']:null;
        
        if(!$ciclo)
            return ['Por favor, informe algum valor no campo Ciclo.'];
        if($pagamento->getValor() <= 0)
            return ['Por favor, informe um valor maior que 0 no campo Valor de Cada Parcela'];
        if($pagamento->getPedido()->getValorTotal() <= 0)
            return ['Não é possível criar Parcelas para um Pedido sem Valor. Adicione Itens ao Pedido para poder adicionar mais Pagamentos'];
        if(!$pagamento->getPedido()->getPagamentoContinuo() && $pagamento->getPedido()->getValorControle() <= 0)
            return ['Não há mais saldo disponível para novos Pagamentos. Adicione mais Itens ao Pedido para poder adicionar mais Pagamentos'];
        if($pagamento->getValor() > $pagamento->getPedido()->getValorTotal())
            return ['O campo Valor não pode ser maior que o Valor Total do Pedido'];
        if($pagamento->getPedido()->getCortesia())
            return ['Não é possível adicionar Pagamentos a um Pedido marcado com CORTESIA'];
        if(($qtdPagamentosEmAberto = count($pagamento->getPedido()->getPagamentosEmAberto())) >= Pedidos::maxPagamentosEmAberto())
            return ['Não é possível adicionar Pagamentos automaticamente a um Pedido com mais de ' . Pedidos::maxPagamentosEmAberto() . ' Pagamentos em Aberto'];
        
        $vencimentos = $this->getParcelasVencimentos($pagamento, $ciclo, $qtdPagamentos);
        if(!count($vencimentos))
            return ['Não foi possível detectar automaticamente a melhor quantidade de Parcelas. Por favor, Informe um valor para o campo Quantidade de Parcelas'];
        
        $this->flash('Vencimentos Levantados: ' . count($vencimentos), 'i'); // apagar depois
        // Remove os pagamentos extras, que passam de 24 pagamentos em aberto
        for ($i = (Pedidos::maxPagamentosEmAberto() - $qtdPagamentosEmAberto); $i < count($vencimentos); $i++)
            unset($vencimentos[$i]);
        
        $this->flash('Vencimentos Permanecidos: ' . count($vencimentos), 'i');// apagar depois
        
        $pagamentos = [];
        $controle = $pagamento->getPedido()->getValorControle();
        for($i = 0; $i < count($vencimentos); $i++) {
            if($i >= Pedidos::maxPagamentosEmAberto()) {
                $this->flash('Apenas ' . Pedidos::maxPagamentosEmAberto() . ' de ' . count($vencimentos)  . ' parcelas foram criadas. O limite máximo de Pagamentos Em Aberto em um Pedido é de ' . Pedidos::maxPagamentosEmAberto() . '.', 'w');
                break;
            }
            $novoPagamento = clone $pagamento;
            $novoPagamento->setVencimento($vencimentos[$i]);
            if(!$pagamento->getPedido()->getPagamentoContinuo()) {
                if($controle - $novoPagamento->getValor() < 0)
                    $novoPagamento->setValor($controle);
                else
                    $controle-= $novoPagamento->getValor();
            }
            $novoPagamento->setValorTotal($novoPagamento->getValor());
            $pagamentos[] = $novoPagamento;
        }
        
        $saved = 0;
        foreach ($pagamentos as $pagamentoPagamento) {
            if(($_pagamento = $this->save($pagamentoPagamento, $pagamentoPagamento)) instanceof Pagamentos) {
                $pedidosMapper->ajustarValoresAddPagamento($pagamento->getPedido(), $_pagamento);
                $saved++;
            } else {
                $this->flash('Pagamento ' . $pagamentoPagamento->view('vencimento') . ' não foi salvo', 'w');
                $this->flash($_pagamento, 'w');
            }
        }
        
        return $saved?$pagamento:$errors;
    }

    public function getParcelasVencimentos(Pagamentos $pagamento, $ciclo, $qtdParcelas = 0) {
        $this->checkEntityClass($pagamento);
        if(!$pagamento->getPedido() || !$pagamento->getPedido()->getId())
            die('PagamentosMapper::getParcelasVencimentos(): Pedido não detectado');
        if(!$ciclo || !array_key_exists($ciclo, Constants::pedidosCiclos()))
            die('PagamentosMapper::getParcelasVencimentos(): Ciclo não existente');
        
        $vencimentos = [];
        $dataAtual = $pagamento->getVencimento();
        $diaAtual = $dataAtual->format('d');
                
        if(!$qtdParcelas) {
            if($pagamento->getPedido()->getPagamentoContinuo() && $pagamento->getPedido()->getPeriodoFim()) {
                $i = 0;
                while ($dataAtual <= $pagamento->getPedido()->getPeriodoFim()) {
                    if($i++ >= Pedidos::maxPagamentosEmAberto())
                        break;
                    $vencimentos[] = clone $dataAtual;
                    if($ciclo == 'mensal')
                        $dataAtual = DateService::addMonthOnly($dataAtual, 1, $diaAtual);
                    else if($ciclo == 'anual')
                        $dataAtual = DateService::addYearOnly($dataAtual, 1, $diaAtual);
                    else if($ciclo == 'semestral')
                        $dataAtual = DateService::addSemesterOnly($dataAtual, 1, $diaAtual);
                    else if($ciclo == 'quinzenal')
                        $dataAtual = DateService::addHalfMonthOnly($dataAtual, 1, $diaAtual);
                    else if($ciclo == 'semanal')
                        $dataAtual->add (new \DateInterval('P1W'));
                    else if($ciclo == '10dias')
                        $dataAtual->add (new \DateInterval('P10D'));
                    else if($ciclo == 'diario')
                        $dataAtual->add (new \DateInterval('P1D'));
                    else if($ciclo == 'bienal')
                        $dataAtual = DateService::addYearOnly($dataAtual, 2, $diaAtual);
                }
                $qtdParcelas = count($vencimentos);
            } else if($pagamento->getPedido()->getPagamentoContinuo()) {
                $qtdParcelas = Pedidos::maxPagamentosEmAberto();
            } else {
                $qtdParcelas = $pagamento->getPedido()->getValorControle() / $pagamento->getValor();
                $qtdParcelas = floor($qtdParcelas) != $qtdParcelas? floor($qtdParcelas) + 1: $qtdParcelas;
            }
        }
        if(!count($vencimentos)) {
            for($i = 0; $i < $qtdParcelas; $i++) {
                $vencimentos[] = clone $dataAtual;
                if($ciclo == 'mensal')
                    $dataAtual = DateService::addMonthOnly($dataAtual, 1, $diaAtual);
                else if($ciclo == 'anual')
                    $dataAtual = DateService::addYearOnly($dataAtual, 1, $diaAtual);
                else if($ciclo == 'semestral')
                    $dataAtual = DateService::addSemesterOnly($dataAtual, 1, $diaAtual);
                else if($ciclo == 'quinzenal')
                    $dataAtual = DateService::addHalfMonthOnly($dataAtual, 1, $diaAtual);
                else if($ciclo == 'semanal')
                    $dataAtual->add(new \DateInterval('P1W'));
                else if($ciclo == '10dias')
                    $dataAtual->add(new \DateInterval('P10D'));
                else if($ciclo == 'diario')
                    $dataAtual->add(new \DateInterval('P1D'));
                else if($ciclo == 'bienal')
                    $dataAtual = DateService::addYearOnly($dataAtual, 2, $diaAtual);
            }
        }
        
        return $vencimentos;
    }
    
    /**************************************************************************************   / REGRA DE NEGÓCIO   ***/
    
}


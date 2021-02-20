<?php

namespace App\Mapper;

use App\Entity\Categorias;
use App\Entity\Colaboradores;
use App\Entity\Lojas;
use App\Entity\Pagamentos;
use App\Entity\Pedidos;
use App\Entity\PedidosItens;
use App\Entity\Pessoas;
use App\Mapper\AbstractMapper;
use App\Services\DateService;
use App\Services\TextService;
use DateTime;

class PedidosMapper extends AbstractMapper
{
    // 'id', 'criacao', 'criador', 'criador_colaborador', 'edicao', 'editor', 'editor_colaborador', 'loja', 'pessoa', 'categoria', 'numero', 'data', 'descricao', 'ficha_declaracao', 'cortesia', 'cortesia_justificativa', 'pagamento_continuo', 'periodo_inicio', 'periodo_fim', 'ciclo', 'primeiro_vencimento', 'auto_parcelas', 'situacao', 'concluido_datahora', 'concluido_login', 'concluido_colaborador', 'concluido_justificativa', 'valor_bruto', 'descontos', 'valor_total', 'valor_controle', 'pagamentos_valor', 'pagamentos_descontos', 'pagamentos_acrescimos', 'pagamentos_valor_total', 'pagamentos_valor_pago', 'comissionado', 'comissionado_quitado', 'cobranca_ignorar', 'cobranca_ignorar_login', 'cobranca_ignorar_colaborador', 'indicador', 'indicador_valor', 'indicador_quitado', 'indicador_quitado_datahora', 'indicador_quitado_login', 'indicador_quitado_colaborador', 'indicador_quitado_data', 'indicador_quitado_valor', 'indicador_obs', 'obs'
    protected $entityClass = Pedidos::class;
    protected $selectFrom = 'p';
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($pedido, $data) { // Pedidos 
        $this->checkEntityClass($pedido);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$pedido->getLoja())
            $errors[] = 'Loja não identificada';
        // Se Cliente for obrigatório verifica se tem cliente
        if(!$this->session()->get('loja')->getPedidosSemCliente() && (!$pedido->getPessoa() && !$data->getPessoa()))
            $errors[] = 'Não é permitido adicionar Pedidos sem Clientes. Por favor, selecione uma Pessoa no campo Cliente';
        // Se não for para a Loja criar os números, então exige um número de pedido
        if($this->session()->get('loja')->getPedidosNumeracao() == 'sem_numeracao') {
            if(!$data->getNumero())
                $errors[] = 'O campo Número não pode estar vazio';
            if(count($this->select(['loja'=>$data->getLoja(), 'numero='=>$data->getNumero(), 'id!='=>$data->getId()])))
                $errors[] = 'Um Pedido com o mesmo número já foi criado. Por favor, informe outro Número para este Pedido';
        }
        // Se o Pedido for Pagamento Continuo bloqueia a alteração pra Não Contínuo
        
        if($pedido->getPagamentoContinuo() && !$data->getPagamentoContinuo()) {
            $data->setPagamentoContinuo($pedido->getPagamentoContinuo());
            $this->flash('Um Pedido configurado como Pagamento Contínuo não pode ser modificado para não Contínuo', 'w');
        }
        if($data->getPagamentoContinuo()) {
            if(!$data->getCiclo())
                $errors[] = 'O campo Ciclo não pode estar vazio';
            if(!$data->getPrimeiroVencimento())
                $errors[] = 'O campo Primeiro Vencimento não pode estar vazio';
        } else
            $data->setPeriodoInicio(null)->setPeriodoFim(null)->setCiclo(null)->setPrimeiroVencimento(null)->setAutoParcelas(false);
        
        if(!$data->getCortesia())
            $data->setCortesiaJustificativa(null);
        
        if($data->view('situacao_ativo?')) {
            $data->setConcluidoDatahora(null)->setConcluidoLogin(null)->setConcluidoColaborador(null)->setConcluidoJustificativa(null);
        } else if ($data->view('situacao_concluido?')) {
            $data->setConcluidoDatahora(new DateTime())->setConcluidoLogin($this->security->getUser())->setConcluidoColaborador($this->session->get('colaborador', null))->setConcluidoJustificativa(null);
        } else if ($data->view('situacao_cancelado?')) {
            if(!$data->getConcluidoJustificativa())
                $errors[] = 'Por favor, informe uma justificativa para o cancelamento do Pedido';
            else
                $data->setConcluidoDatahora(new DateTime())->setConcluidoLogin($this->security->getUser())->setConcluidoColaborador($this->session->get('colaborador', null));
        }
        
        if(count($errors)) {            
            return $errors;
        }
        
        $pedido->setCategoria($data->getCategoria())
                ->setData($data->getData())
                ->setDescricao($data->getDescricao())
                ->setCortesia($data->getCortesia())
                ->setCortesiaJustificativa($data->getCortesiaJustificativa())
                ->setPagamentoContinuo($data->getPagamentoContinuo())
                ->setPeriodoInicio($data->getPeriodoInicio())
                ->setPeriodoFim($data->getPeriodoFim())
                ->setCiclo($data->getCiclo())
                ->setPrimeiroVencimento($data->getPrimeiroVencimento())
                ->setAutoParcelas($data->getAutoParcelas())
                ->setComissionado($data->getComissionado())
                ->setIndicador($data->getIndicador())
                ->setObs($data->getObs())
                ;
        // Alternar Cliente, apenas se for GERENTE...
        if($pedido->getId() && $pedido->getPessoa() != $data->getPessoa() && $data->getPessoa()) {
            if($this->security()->isGranted('ROLE_LOJA_GERENTE')) {
                $pedido->setPessoa($data->getPessoa());
            } else {
                $this->flash('Não foi possível alterar o Cliente. Acesso não permitido!', 'w');
            }
        } else if(!$pedido->getId()) {
            $pedido->setPessoa($data->getPessoa()?$data->getPessoa():$pedido->getPessoa());
        }
        // Configuração e troca de número
        if($this->session()->get('loja')->getPedidosNumeracao() == 'sem_numeracao') {
            $pedido->setNumero($data->getNumero());
        } else if(!$pedido->getId()) {
            if(is_array($novoNumero = $this->proximoNumero())) {
                var_dump($novoNumero);
                die('Não foi possível gerar um novo número de Pedido');
            } else
                $pedido->setNumero($novoNumero);
        }
        // Alternar Situação apenas se for Pedido Novo ou se for para Cancelar ou Concluir
        $setSituacao = false;
        if($pedido->getId() && $pedido->getSituacao() != $data->getSituacao()) {
            if($data->view('situacao_concluido?') || $data->view('situacao_cancelado?')) {
                $setSituacao = true;
            } else {
                $this->flash('Não é possível reativar o Pedido através do Formulário de Pedido', 'w');
            }
        } else if(!$pedido->getId()) {
            $setSituacao = true;
        }
        if($setSituacao) {
            $pedido->setSituacao($data->getSituacao())
                    ->setConcluidoDatahora($data->getConcluidoDatahora())
                    ->setConcluidoLogin($data->getConcluidoLogin())
                    ->setConcluidoColaborador($data->getConcluidoColaborador())
                    ->setConcluidoJustificativa($data->getConcluidoJustificativa());
        }
        
        if($pedido->getId()) {
            $pedido->setEdicao(new DateTime())
                    ->setEditor($this->security->getUser())
                    ->setEditorColaborador($this->session->get('colaborador', null));
        } else {
            $pedido->setCriacao(new DateTime())
                    ->setCriador($this->security->getUser())
                    ->setCriadorColaborador($this->session->get('colaborador', null))
                    ->setValorBruto(0)->setDescontos(0)->setValorTotal(0)->setValorControle(0)
                    ->setPagamentosValor(0)->setPagamentosDescontos(0)->setPagamentosAcrescimos(0)->setPagamentosValorTotal(0)
                    ->setPagamentosValorPago(0);
        }
        
        return $this->_save($pedido);
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
            } else if ($atrib == 'pessoa') {
                if($valor instanceof Pessoas)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.pessoa = :pessoa')->setParameter('pessoa', (int)$valor);
            } else if ($atrib == 'categoria') {
                if($valor instanceof Categorias)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.categoria = :categoria')->setParameter('categoria', (int)$valor);
            } else if ($atrib == 'categoria_chain') {
                if($valor instanceof Categorias)
                    $query->andWhere($from . '.categoria in (:categoria_chain)')->setParameter('categoria_chain', $valor->view('chain_id'));
            } else if ($atrib == 'categoria_is_null') {
                if ($valor != 0)
                    $query->andWhere($from . '.categoria is null');
            } else if ($atrib == 'numero=') {
                if ($valor != '')
                    $query->andWhere($from . '.numero = :numero_equal')->setParameter('numero_equal', $valor);
            } else if ($atrib == 'data') {
                if($valor instanceof DateTime)
                    $query->andWhere($from . '.data = :data')->setParameter('data', $valor);
                else if ($valor != '')
                    $query->andWhere($from . '.data = :data')->setParameter('data', DateService::converte($valor, 'en'));
            } else if ($atrib == 'data_de') {
                if($valor instanceof DateTime)
                    $query->andWhere($from . '.data >= :data_de')->setParameter('data_de', $valor);
                else if ($valor != '')
                    $query->andWhere($from . '.data >= :data_de')->setParameter('data_de', DateService::converte($valor, 'en'));
            } else if ($atrib == 'data_ate') {
                if($valor instanceof DateTime)
                    $query->andWhere($from . '.data <= :data_ate')->setParameter('data_ate', $valor);
                else if ($valor != '')
                    $query->andWhere($from . '.data <= :data_ate')->setParameter('data_ate', DateService::converte($valor, 'en'));
            } else if ($atrib == 'situacao') {
                if ($valor != 0)
                    $query->andWhere($from . '.situacao = :situacao')->setParameter('situacao', (int)$valor);
            } else if ($atrib == 'comissionado') {
                if($valor instanceof Colaboradores)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.comissionado = :comissionado')->setParameter('comissionado', (int)$valor);
            } else if ($atrib == 'indicador') {
                if($valor instanceof Pessoas)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.indicador = :indicador')->setParameter('indicador', (int)$valor);
            } else if ($atrib == 'nome_apelido_razao_social') { /***   nok   ***/
                if ($valor != '')
                    $query->andWhere($from . '.nome like :nome_apelido_razao_social or ' . $from . '.apelido like :nome_apelido_razao_social or ' . $from . '.razaoSocial like :nome_apelido_razao_social')->setParameter('nome_apelido_razao_social', TextService::spaceToPercent($valor));
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
    
    public function proximoNumero() :string
    {
        $erros = [];
        if(!$this->session()->get('loja'))
            return ['Loja não detectada'];
        $novoNumero = null;
        $ultimoPedido = null;
        
        if(count($pedidos = $this->select(['loja' => $this->session()->get('loja'), 'limit' => 1], ['id' => 'desc'])))
            $ultimoPedido = $pedidos[0];
        if($ultimoPedido)
            $novoNumero = ((int)$ultimoPedido->getNumero()) + 1;
        
        if(!$novoNumero && $this->session()->get('loja')->getPedidosNumeracaoInicial())
            $novoNumero = (int)$this->session()->get('loja')->getPedidosNumeracaoInicial();
        else if(!$novoNumero)
            $novoNumero = 1;
        
        if($this->session()->get('loja')->getPedidosNumeracao() == 'sequencia_ano')
            $novoNumero = $novoNumero . '/' . date('Y');
        else if($this->session()->get('loja')->getPedidosNumeracao() == 'sequencia_ano_reiniciando') {
            if($ultimoPedido) {
                $numeroArray = explode('/', $ultimoPedido->getNumero());
                $novoNumero = $numeroArray[1] != date('Y')?1:$novoNumero;
                $novoNumero = $novoNumero . '/' . date('Y');
            } else {
                $novoNumero = $novoNumero . '/' . date('Y');
            }
        } else if($this->session()->get('loja')->getPedidosNumeracao() == 'sem_numeracao')
            die('Número automático não habilitado para esta loja/conta');
        
        return $novoNumero;
    }
    
    public function ajustarValoresAddItem(Pedidos $pedido, PedidosItens $pedidoItem, PedidosItens $oldPedidoItem = null)
    {
        $this->checkEntityClass($pedido, true);
        if(!$pedidoItem->getId())
            die('PedidosMapper::ajustarValoresAddItem: Não foi possível identificar o Pedido Item');
        if($oldPedidoItem && $oldPedidoItem->getId() && !$oldPedidoItem->getSemValor()) {
            $pedido->setValorBruto($pedido->getValorBruto() - $oldPedidoItem->getValorTotalSemDesconto());
            $pedido->setDescontos($pedido->getDescontos() - $oldPedidoItem->getDesconto());
            $pedido->setValorTotal($pedido->getValorTotal() - $oldPedidoItem->getValorTotal());
            if(!$pedido->getPagamentoContinuo())
                $pedido->setValorControle($pedido->getValorControle() - $oldPedidoItem->getValorTotal());
        }
        if($pedidoItem && $pedidoItem->getId() && !$pedidoItem->getSemValor()) {
            $pedido->setValorBruto($pedido->getValorBruto() + $pedidoItem->getValorTotalSemDesconto());
            $pedido->setDescontos($pedido->getDescontos() + $pedidoItem->getDesconto());
            $pedido->setValorTotal($pedido->getValorTotal() + $pedidoItem->getValorTotal());
            if(!$pedido->getPagamentoContinuo())
                $pedido->setValorControle($pedido->getValorControle() + $pedidoItem->getValorTotal());
        }
        
        $this->_save($pedido);
    }
    
    public function ajustarValoresRemoverItem(Pedidos $pedido, PedidosItens $pedidoItem)
    {
        $this->checkEntityClass($pedido, true);
        if(!$pedidoItem->getSemValor()) {
            $pedido->setValorBruto($pedido->getValorBruto() - $pedidoItem->getValorTotalSemDesconto());
            $pedido->setDescontos($pedido->getDescontos() - $pedidoItem->getDesconto());
            $pedido->setValorTotal($pedido->getValorTotal() - $pedidoItem->getValorTotal());
            if(!$pedido->getPagamentoContinuo())
                $pedido->setValorControle($pedido->getValorControle() - $pedidoItem->getValorTotal());
        }
        
        $this->_save($pedido);
    }
    
    public function ajustarValoresAddPagamento(Pedidos $pedido, Pagamentos $pagamento, Pagamentos $oldPagamento = null)
    {
        $this->checkEntityClass($pedido, true);
        if(!$pagamento->getId())
            die('PedidosMapper::ajustarValoresAddPagamento: Não foi possível identificar o Pagamento');
        
        if($oldPagamento && $oldPagamento->getId()) {
            if(!$pedido->getPagamentoContinuo())
                $pedido->setValorControle($pedido->getValorControle() + $oldPagamento->getValor());
            $pedido->setPagamentosValor($pedido->getPagamentosValor() - $oldPagamento->getValor());
            $pedido->setPagamentosDescontos($pedido->getPagamentosDescontos() - $oldPagamento->getDesconto());
            $pedido->setPagamentosValorTotal($pedido->getPagamentosValorTotal() - $oldPagamento->getValorTotal());
            $pedido->setPagamentosAcrescimos($pedido->getPagamentosAcrescimos() - $oldPagamento->getPagamentoAcrescimos());
            $pedido->setPagamentosValorPago($pedido->getPagamentosValorPago() - $oldPagamento->getPagamentoValor());
        }
        
        if($pagamento && $pagamento->getId()) {
            if(!$pedido->getPagamentoContinuo())
                $pedido->setValorControle($pedido->getValorControle() - $pagamento->getValor());
            $pedido->setPagamentosValor($pedido->getPagamentosValor() + $pagamento->getValor());
            $pedido->setPagamentosDescontos($pedido->getPagamentosDescontos() + $pagamento->getDesconto());
            $pedido->setPagamentosValorTotal($pedido->getPagamentosValorTotal() + $pagamento->getValorTotal());
            $pedido->setPagamentosAcrescimos($pedido->getPagamentosAcrescimos() + $pagamento->getPagamentoAcrescimos());
            $pedido->setPagamentosValorPago($pedido->getPagamentosValorPago() + $pagamento->getPagamentoValor());
        }
        
        $this->_save($pedido);
    }
    
    public function ajustarValoresRemoverPagamento(Pedidos $pedido, Pagamentos $pagamento)
    {
        $this->checkEntityClass($pedido, true);
        
        if(!$pedido->getPagamentoContinuo())
            $pedido->setValorControle($pedido->getValorControle() + $pagamento->getValor());
        $pedido->setPagamentosValor($pedido->getPagamentosValor() - $pagamento->getValor());
        $pedido->setPagamentosDescontos($pedido->getPagamentosDescontos() - $pagamento->getDesconto());
        $pedido->setPagamentosValorTotal($pedido->getPagamentosValorTotal() - $pagamento->getValorTotal());
        $pedido->setPagamentosAcrescimos($pedido->getPagamentosAcrescimos() - $pagamento->getPagamentoAcrescimos());
        $pedido->setPagamentosValorPago($pedido->getPagamentosValorPago() - $pagamento->getPagamentoValor());
        
        $this->_save($pedido);
    }
    
    public function ajustarValoresCancelarPagamento(Pedidos $pedido, Pagamentos $pagamento, $reverterCancelamento = false)
    {
        $this->checkEntityClass($pedido, true);
        if(!$pagamento->getId())
            die('PedidosMapper::ajustarValoresCancelarPagamento: Não foi possível identificar o Pagamento');
        
        if($reverterCancelamento) {
            $pedido->setPagamentosDescontos($pedido->getPagamentosDescontos() + $pagamento->getDesconto());
            $pedido->setPagamentosValorTotal($pedido->getPagamentosValorTotal() + $pagamento->getValorTotal());
            $pedido->setPagamentosAcrescimos($pedido->getPagamentosAcrescimos() + $pagamento->getPagamentoAcrescimos());
            $pedido->setPagamentosValorPago($pedido->getPagamentosValorPago() + $pagamento->getPagamentoValor());
        } else {
            $pedido->setPagamentosDescontos($pedido->getPagamentosDescontos() - $pagamento->getDesconto());
            $pedido->setPagamentosValorTotal($pedido->getPagamentosValorTotal() - $pagamento->getValorTotal());
            $pedido->setPagamentosAcrescimos($pedido->getPagamentosAcrescimos() - $pagamento->getPagamentoAcrescimos());
            $pedido->setPagamentosValorPago($pedido->getPagamentosValorPago() - $pagamento->getPagamentoValor());
        }
        
        $this->_save($pedido);
    }
    
    public function addPagamentoAutomatico(Pedidos $pedido, PagamentosMapper $pagamentosMapper)
    {
        // Mantém sempre 5 pagamento em aberto até o final do pedido, se a criação automática estiver habilitada
        $this->checkEntityClass($pedido, true);
        if(!$pedido->getPagamentoContinuo() || !$pedido->getAutoParcelas())
            return false;
        $qtdParcelasEmAberto = 5;
        $pagamentosEmAberto = $pedido->getPagamentosEmAberto($qtdParcelasEmAberto, 'DESC');
        if(count($pagamentosEmAberto) >= $qtdParcelasEmAberto)
            return false;
        if(!$ultimoPagamento = $pagamentosEmAberto->first()){
            $pagamentosQuitados = $pedido->getPagamentosQuitados(1, 'DESC');
            if(!$ultimoPagamento = $pagamentosQuitados->first())
                return false;
        }
        $pgtos = $qtdParcelasEmAberto - count($pagamentosEmAberto);
        $vencimentos = $pagamentosMapper->getParcelasVencimentos($ultimoPagamento, $pedido->getCiclo(), $pgtos + 1);
        array_shift($vencimentos);
        foreach ($vencimentos as $vencimento) {
            if($vencimento > $pedido->getPeriodoFim())
                continue;
            $pagamento = new Pagamentos();
            $pagamento->setLoja($ultimoPagamento->getLoja())
                    ->setPessoa($ultimoPagamento->getPessoa())
                    ->setPedido($ultimoPagamento->getPedido())
                    ->setVencimento($vencimento)
                    ->setValor($pedido->getValorTotal())
                    ->setForma($ultimoPagamento->getForma())
                    ->setDescontoVencimento($ultimoPagamento->getDescontoVencimento())
                    ->setDescontoVencimentoPercentual($ultimoPagamento->getDescontoVencimentoPercentual())
                    ->setMulta($ultimoPagamento->getMulta())
                    ->setJuros($ultimoPagamento->getJuros())
                    ->setObs('* Pagamento criado automaticamente pelo sistema em ' . (DateService::now('pt')))
                    ->setComissionado($ultimoPagamento->getComissionado())
                    ;
            $_pagamento = clone $pagamento;
            if(($_pagamento = $pagamentosMapper->save($pagamento, $_pagamento)) instanceof Pagamentos) {
                $this->ajustarValoresAddPagamento($pedido, $pagamento);
            } else {
                $this->flash('Não foi possível salvar o pagamento ' . $pagamento->view('vencimento'), 'w');
                $this->flash($pagamento, 'w');
            }
        }
        
        return true;
    }
    
    /**************************************************************************************   / REGRA DE NEGÓCIO   ***/
    
}


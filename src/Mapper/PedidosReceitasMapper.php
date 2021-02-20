<?php

namespace App\Mapper;

use App\Entity\PedidosReceitas;
use App\Mapper\AbstractMapper;
use App\Services\Constants;
use App\Services\DateService;
use App\Services\FilesService;
use App\Services\TextService;
use DateTime;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PedidosReceitasMapper extends AbstractMapper
{
    // id, criacao, criador, criador_colaborador, edicao, editor, editor_colaborador, pedido, pessoa, pessoa_receita, numero, altura, itens, situacao, concluido_datahora, concluido_login, concluido_colaborador, concluido_justificativa, obs, obs_laboratorio,     
    protected $entityClass = PedidosReceitas::class;
    protected $selectFrom = 'pr';
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($pedidoReceita, $data) { // PedidosReceitas 
        $this->checkEntityClass($pedidoReceita);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$pedidoReceita->getPedido() || !$pedidoReceita->getPedido()->getId()) {
            $errors[] = 'O Pedido desta Receita não foi identificado';
        }
        if(!$pedidoReceita->getPessoa() || !$pedidoReceita->getPessoa()->getId()) {
            $errors[] = 'Não foi possível associar o serviço a uma Pessoa';
        }
        
        if(count($errors)) {            
            return $errors;
        }
        
        $pedidoReceita->setAltura($data->getAltura())
                ->setObs($data->getObs())
                ->setObsLaboratorio($data->getObsLaboratorio())
                ;
        
        if($pedidoReceita->getId()) {
            $pedidoReceita->setEdicao(new DateTime())
                    ->setEditor($this->security->getUser())
                    ->setEditorColaborador($this->session->get('colaborador', null));
        } else {
            $pedidoReceita->setCriacao(new DateTime())
                    ->setCriador($this->security->getUser())
                    ->setCriadorColaborador($this->session->get('colaborador', null))
                    ->setNumero($this->proximoNumero($pedidoReceita->getPedido()))
                    ->setSituacao(1)
                    ;
        }
        
        return $this->_save($pedidoReceita);
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
            } else if($table == 'pessoas_receitas') {
                $pessoas_receitas_from = 'per';
                $query->innerJoin($from . '.pessoaReceita', $pessoas_receitas_from)->addSelect($pessoas_receitas_from);
            } else if($table == 'pessoas') {
                $pessoas_from = 'pe';
                $query->innerJoin($pessoas_receitas_from . '.pessoa', $pessoas_from)->addSelect($pessoas_from);
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
            } else if ($atrib == 'pessoa_receita') {
                if($valor instanceof \App\Entity\PessoasReceitas)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.pessoaReceita = :pessoaReceita')->setParameter('pessoaReceita', (int)$valor);
            } else if ($atrib == 'limit') {
                if (is_numeric($valor))
                    $query->setMaxResults($valor);
            } else if ($atrib == 'pedido_loja') {
                if($valor instanceof \App\Entity\Lojas)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($pedidos_from . '.loja = :pedido_loja')->setParameter('pedido_loja', (int)$valor);
            } else if ($atrib == 'pessoas_receitas_pessoa') {
                if($valor instanceof \App\Entity\Pessoas)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($pessoas_receitas_from . '.pessoa = :pessoas_receitas_pessoa')->setParameter('pessoas_receitas_pessoa', (int)$valor);
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
    
    public function savePedidoReceita(PedidosReceitas $pedidoReceita, $form, PessoasMapper $pessoasMapper, PessoasReceitasMapper $pessoasReceitasMapper, $arquivoUuid, $semReceita) {
        $this->checkEntityClass($pedidoReceita);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$semReceita || $pedidoReceita->getPessoaReceita()) {
            $pessoaReceita = $pedidoReceita->getPessoaReceita()?$pedidoReceita->getPessoaReceita():new \App\Entity\PessoasReceitas();
            $_pessoaReceita = clone $pessoaReceita;
            $_pessoaReceita->setDataConsulta($form->get('data_consulta')->getData())
                    ->setOftalmologista($form->get('oftalmologista')->getData())
                    ->setLongeOdEsf($form->get('longe_od_esf')->getData())
                    ->setLongeOdCil($form->get('longe_od_cil')->getData())
                    ->setLongeOdEixo($form->get('longe_od_eixo')->getData())
                    ->setLongeOeEsf($form->get('longe_oe_esf')->getData())
                    ->setLongeOeCil($form->get('longe_oe_cil')->getData())
                    ->setLongeOeEixo($form->get('longe_oe_eixo')->getData())
                    ->setAdicao($form->get('adicao')->getData())
                    ->setPertoOdEsf($form->get('perto_od_esf')->getData())
                    ->setPertoOdCil($form->get('perto_od_cil')->getData())
                    ->setPertoOdEixo($form->get('perto_od_eixo')->getData())
                    ->setPertoOeEsf($form->get('perto_oe_esf')->getData())
                    ->setPertoOeCil($form->get('perto_oe_cil')->getData())
                    ->setPertoOeEixo($form->get('perto_oe_eixo')->getData())
                    ->setObs($form->get('pessoa_receita_obs')->getData())
                    ;
            if(!$_pessoaReceita->getId())
                $_pessoaReceita->setPessoa($form->get('pessoa')->getData());
            
            $pessoasReceitasMapper->calcGrauDePerto($_pessoaReceita);
            if(($_pessoaReceita = $pessoasReceitasMapper->save($pessoaReceita, $_pessoaReceita)) instanceof \App\Entity\PessoasReceitas) {
                $pessoasReceitasMapper->saveArquivoSession($_pessoaReceita, $arquivoUuid);
            } else {
                return $_pessoaReceita;
            }
            
            $_pessoaReceita->getPessoa()->setOticaDp($form->get('dp')->getData()?$form->get('dp')->getData():$_pessoaReceita->getPessoa()->getOticaDp());
            $pessoasMapper->_save($_pessoaReceita->getPessoa());
        } else {
            $_pessoaReceita = null;
            $pedidoReceita->getPessoa()->setOticaDp($form->get('dp')->getData()?$form->get('dp')->getData():$pedidoReceita->getPessoa()->getOticaDp());
            $pessoasMapper->_save($pedidoReceita->getPessoa());
        } 
        
        $pedidoReceita->setPessoaReceita($_pessoaReceita);
        $pedidoReceita->setPessoa($_pessoaReceita?$_pessoaReceita->getPessoa():$form->get('pessoa')->getData());
        $_pedidoReceita = clone $pedidoReceita;
        $_pedidoReceita->setAltura($form->get('altura')->getData())
                ->setObs($form->get('obs')->getData())
                ->setObsLaboratorio($form->get('obs_laboratorio')->getData())
                ;
        
        return $this->save($pedidoReceita, $_pedidoReceita);
    }

    public function proximoNumero(\App\Entity\Pedidos $pedido) :?int
    {
        $erros = [];
        if(!$pedido || !$pedido->getId())
            return ['Pedido não detectado'];
        if(count($pedidosReceitas = $this->select(['pedido' => $pedido, 'limit' => 1], ['id' => 'desc']))) {
            return $pedidosReceitas[0]->getNumero() + 1;
        }
        return 1;
    }
    
    public function reordenarNumeros(PedidosReceitas $pedidoReceita, $pos = null)
    {
        $index = $pedidoReceita->getNumero() - 1;
        $newIndex = $pos == 'subir'?$index - 1:($pos == 'descer'?$index + 1:(is_numeric($pos)?$pos - 1:-1));
        
        if($pedidoReceita->getPedido()) {
            $i = 0;
            $itens = $pedidoReceita->getPedido()->getPedidosItensByNumero();
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


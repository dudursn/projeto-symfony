<?php

namespace App\Mapper;

use App\Entity\Mensagens;
use App\Mapper\AbstractMapper;
use App\Services\TextService;
use DateTime;

class MensagensMapper extends AbstractMapper
{
    // 'id', 'criacao', 'login', 'colaborador', 'mensagem', 'destinatario', 'destinatarios', 'assunto', 'conteudo', 'email', 'lida', 'login_arquivada', 'destinatario_arquivada'
    protected $entityClass = Mensagens::class;
    protected $selectFrom = 'm';
    protected $loginsMapper;
    protected $colaboradoresMapper;
        
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($mensagem, $data) { // Mensagens 
        $this->checkEntityClass($mensagem);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$mensagem->getDestinatario())
            $errors[] = 'Destinatário não identificado';
        if(!$data->getAssunto())
            $errors[] = 'O campo Assunto não pode estar vazio';
        if(!$data->getConteudo())
            $errors[] = 'O campo Conteúdo não pode estar vazio';
        if($data->getMensagem())
            $data->setDestinatarios('');
        if(!$mensagem->getId() && $data->getEmail())
            $data->setEmail(1);
        
        if(count($errors)) {
            return $errors;
        }
        
        $mensagem->setLogin($data->getLogin())
                ->setColaborador($data->getColaborador())
                ->setMensagem($data->getMensagem())
                ->setDestinatario($data->getDestinatario())
                ->setDestinatarios($data->getDestinatarios())
                ->setAssunto($data->getAssunto())
                ->setConteudo($data->getConteudo())
                ->setEmail($data->getEmail())
                ->setLida($data->getLida())
                ->setLoginArquivada($data->getLoginArquivada())
                ->setDestinatarioArquivada($data->getDestinatarioArquivada())
            ;
        
        if(!$mensagem->getId()) {
            $mensagem->setCriacao(new DateTime())
                    ->setLida(false)
                    ->setLoginArquivada(false)
                    ->setDestinatarioArquivada(false);
        }
        
        return $this->_save($mensagem);
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
            } else if ($atrib == 'login') {
                if($valor instanceof \App\Entity\Logins)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.login = :login')->setParameter('login', (int)$valor);
            } else if ($atrib == 'colaborador') {
                if($valor instanceof \App\Entity\Colaboradores)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.colaborador = :colaborador')->setParameter('colaborador', (int)$valor);
            } else if ($atrib == 'mensagem') {
                if($valor instanceof Mensagens)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.mensagem = :mensagem')->setParameter('mensagem', (int)$valor);
            } else if ($atrib == 'mensagem_is_null') {
                if ($valor != 0)
                    $query->andWhere($from . '.mensagem is null');
            } else if ($atrib == 'destinatario') {
                if($valor instanceof \App\Entity\Logins)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.destinatario = :destinatario')->setParameter('destinatario', (int)$valor);
            } else if ($atrib == 'assunto_conteudo') {
                if ($valor != '')
                    $query->andWhere($from . '.assunto like :assunto_conteudo or ' . $from . '.conteudo like :assunto_conteudo')->setParameter('assunto_conteudo', TextService::spaceToPercent($valor));
            } else if ($atrib == 'lida_is_null') {
                if ($valor != 0)
                    $query->andWhere($from . '.lida is null or ' . $from . '.lida = 0');
            } else if ($atrib == 'loginArquivada') {
                if ($valor != 0)
                    $query->andWhere($from . '.loginArquivada = :loginArquivada')->setParameter('loginArquivada', (int)$valor);
            } else if ($atrib == 'destinatarioArquivada') {
                if ($valor != 0)
                    $query->andWhere($from . '.destinatarioArquivada = :destinatarioArquivada')->setParameter('destinatarioArquivada', (int)$valor);
            } else if ($atrib == 'loginArquivada_is_null') {
                if ($valor != 0)
                    $query->andWhere($from . '.loginArquivada is null or ' . $from . '.loginArquivada = 0');
            } else if ($atrib == 'destinatarioArquivada_is_null') {
                if ($valor != 0)
                    $query->andWhere($from . '.destinatarioArquivada is null or ' . $from . '.destinatarioArquivada = 0');
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
        foreach ($dependencies as $dependency) {
            if($dependency instanceof LoginsMapper)
                $this->loginsMapper = $dependency;
            if($dependency instanceof ColaboradoresMapper)
                $this->colaboradoresMapper = $dependency;
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
        
        return $this;
        */
    }

    /*************************************************************************************   / MÉTODOS ABSTRATOS   ***/
    
    /******************************************************************************************   REGRA DE NEGÓCIO   ***/
    
    /*
     * @params DESTINATARIOS
     */
    public function enviarMensagem(Mensagens $mensagem, $destinatarios, $notificacao = false)
    {
        $this->checkEntityClass($mensagem);
        if(!$destinatarios) {
            return ['Nenhum destinatário identificado'];
        } else if (!is_array($destinatarios)) {
            $destinatarios = [$destinatarios];
        }
        $errors = array(); // VETOR DE ERROS: $errors[] = '______';
        
        if($notificacao) {
            $mensagem->setLogin(null);
            $mensagem->setColaborador(null);
        } else {
            $mensagem->setLogin($this->security->getUser());
            $mensagem->setColaborador($this->session->get('colaborador', null));
        }
        
        if(!$this->loginsMapper || !$this->colaboradoresMapper)
            die ('MensagensMapper::enviarMensagem(): É necessário injetar LoginsMapper e ColaboradoresMapper para o processamento de novas mensagens');
        
        $logins = []; // pra não enviar duas mensagens repetidas para o mesmo login
        //$mensagens = array();
        $destinatarios_json = array();
        foreach ($destinatarios as $destinatario) {
            switch ($destinatario) {
                case ($destinatario instanceof \App\Entity\Colaboradores) && $destinatario->getId():
                    // SE FOR COLABORADOR -------------------------------------------------------------------------------
                    foreach ($destinatario->getLoginsColaboradores() as $loginColaborador) {
                        if (!array_key_exists($loginColaborador->getLogin()->getId(), $logins)) {
                            $logins[$loginColaborador->getLogin()->getId()] = $loginColaborador->getLogin();
                        }
                    }
                    $destinatarios_json[] = array('login' => null, 'colaborador' => $destinatario->getId(), 'nome' => $destinatario->view('identificacao_nome') . ' (' . $destinatario->getCargo() . ')');
                    break;
                case ($destinatario instanceof \App\Entity\Logins) && $destinatario->getId():
                    // SE FOR LOGIN ----------------------------------------------------------------------------------
                    if (!array_key_exists($destinatario->getId(), $logins)) {
                        $logins[$destinatario->getId()] = $destinatario;
                    }
                    $destinatarios_json[] = array('login' => $destinatario->getId(), 'colaborador' => null, 'nome' => $destinatario->getNome() . ' (' . $destinatario->view('role_nome') . ')');
                    break;
                case substr($destinatario, 0, 12) == 'Colaborador:':
                    // SE FOR COLABORADOR -------------------------------------------------------------------------------
                    $colaborador = $this->colaboradoresMapper->find(str_replace('Colaborador:', '', $destinatario));
                    foreach ($colaborador->getLoginsColaboradores() as $loginColaborador) {
                        if (!array_key_exists($loginColaborador->getLogin()->getId(), $logins)) {
                            $logins[$loginColaborador->getLogin()->getId()] = $loginColaborador->getLogin();
                        }
                    }
                    $destinatarios_json[] = array('login' => null, 'colaborador' => $colaborador->getId(), 'nome' => $colaborador->view('identificacao_nome') . ' (' . $colaborador->getCargo() . ')');
                    break;
                case substr($destinatario, 0, 6) == 'Login:':
                    // SE FOR LOGIN ----------------------------------------------------------------------------------
                    $login = $this->loginsMapper->find(str_replace('Login:', '', $destinatario));
                    if (!array_key_exists($login->getId(), $logins)) {
                        $logins[$login->getId()] = $login;
                    }
                    $destinatarios_json[] = array('login' => $login->get('id'), 'colaborador' => null, 'nome' => $login->view('nome') . ' (' . $login->view('role_nome') . ')');
                    break;
                default :
                    var_dump($destinatario);
                    die('Tipo de Destinatário não identificado');
                    break;
            }
        }
        
        if(!count($logins)) {
            return ['Não foi possível identificar nenhum destinatário'];
        }
        
        $mensagemPrincipal = null;
        foreach ($logins as $login) {
            $_mensagem = new Mensagens();
            $_mensagem->setLogin($mensagem->getLogin())
                    ->setColaborador($mensagem->getColaborador())
                    ->setDestinatario($login)
                    ->setAssunto($mensagem->getAssunto())
                    ->setConteudo($mensagem->getConteudo())
                    ->setEmail($mensagem->getEmail());
            if($mensagemPrincipal)
                $_mensagem->setMensagem($mensagemPrincipal);
            else
                $_mensagem->setDestinatarios(json_encode($destinatarios_json));
            
            if (($_mensagem = $this->save($_mensagem, $_mensagem)) instanceof Mensagens) {
                $mensagemPrincipal = $mensagemPrincipal?$mensagemPrincipal:$_mensagem;
            } else {
                return $_mensagem;
            }
        }
        
        if($mensagemPrincipal->getEmail()) {
            $to = [];
            foreach ($logins as $login)
                $to[$login->getEmail()] = $login->getNome();
            $this->mail->sendMensagem($mensagemPrincipal, $to);
        }
        
        return $mensagemPrincipal;
    }

    public function toggleLida ($mensagem, $valor = null, $msm = true) {
        if(!($mensagem instanceof Mensagens))
            exit('MensagensMapper::toggleLida: Tipo de mensagem inválido');
        if(!$mensagem->getId())
            exit('MensagensMapper::toggleLida: Mensagem sem id');
        
        if(!$valor) {
            $valor = $mensagem->getLida()?false:true;
        }
        $mensagem->setLida($valor);
        $this->_save($mensagem);
        if($msm && $valor)
            $this->flash ('Mensagem marcada como lida');
        else if($msm)
            $this->flash ('Mensagem marcada como NÃO lida');
        return TRUE;
    }
    
    public function toggleArquivada ($mensagem, $campo_arquivada) {
        if(!($mensagem instanceof Mensagens))
            exit('MensagensMapper::toggleArquivada: Tipo de mensagem inválido');
        if(!$mensagem->getId())
            exit('MensagensMapper::toggleArquivada: Mensagem sem id');
        
        if($campo_arquivada == 'login') {
           if(!$mensagem->isLoginLogado())
               exit('MensagensMapper::toggleArquivada: isLoginLogado failed');
           $value = $mensagem->getLoginArquivada()?false:true;
           if($mensagem->getMensagem()) {
               $mensagem->getMensagem()->setLoginArquivada($value);
               foreach ($mensagem->getMensagem()->getMensagens() as $item)
                   $item->setLoginArquivada($value);
           } else {
               $mensagem->setLoginArquivada($value);
               foreach ($mensagem->getMensagens() as $item)
                   $item->setLoginArquivada($value);
           }
           $this->_save($mensagem);
        } else if($campo_arquivada == 'destinatario') {
           if(!$mensagem->isDestinatarioLogado())
               exit('MensagensMapper::toggleArquivada: isDestinatarioLogado failed');
           $value = $mensagem->getDestinatarioArquivada()?false:true;
           $mensagem->setDestinatarioArquivada($value);
           $this->_save($mensagem);
        } else {
            exit('Application_Model_MensagensMapper::toggleArquivada: campo_arquivada não identificado');
        }
        return true;
    }
    
    public function userNavigation() {
        $data = array('mensagens' => array(), 'nao_lidas_qtd' => 0);
        if(!$this->security->getUser())
            return $data;
        foreach (($data['mensagens'] = $this->select(['destinatario' => $this->security->getUser()->getId(), 'destinatarioArquivada_is_null' => 1, 'limit' => 6], ['lida' => 'ASC', 'id' => 'DESC'])) as $item) {
            if(!$item->getLida())
               $data['nao_lidas_qtd']++;
        }
        if(isset($data['mensagens'][5]))
            unset ($data['mensagens'][5]);
        return $data;
    }
    
    public function incrementaEmail (Mensagens $mensagem) {
        if(!$mensagem->getId())
            return;
        $mensagem->setEmail($mensagem->getEmail() + 1);
        $this->_save($mensagem);
    }
    
    /****************************************************************************************   / REGRA DE NEGÓCIO   ***/
    
}


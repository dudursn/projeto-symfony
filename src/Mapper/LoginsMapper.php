<?php

namespace App\Mapper;

use App\Entity\Logins;
use App\Services\FlashMessages;
use App\Services\TextService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class LoginsMapper extends AbstractMapper
{
    // id, criacao, criador, edicao, editor, email, pass, nome, role, hash, hash_criacao, confirmado, ativo, 
    protected $entityClass = Logins::class;
    protected $selectFrom = 'l';
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($login, $data) {
        $this->checkEntityClass($login);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$data->getRole())
            $errors[] = 'Selecione um Nível de Acesso';
        
        if(!$login->getId() && !$data->getPass())
            $errors[] = 'É preciso informar uma senha para um novo Login';
        
        if(count($this->select(['email=' => $data->getEmail(), 'id!=' => $login->getId()])))
            $errors[] = 'Login já existente. Por favor, informe um novo E-mail';
        
        if(count($errors)) {
            return $errors;
        }
        
        $login->setEmail($data->getEmail())->setNome($data->getNome())->setRole($data->getRole())
                ->setConfirmado($data->getConfirmado())->setAtivo($data->getAtivo());
        
        if($data->getPass())
            $login->setPass($this->passwordEncoder->encodePassword($login, $data->getPass()));
        
        if($login->getId()) {
            $login->setEdicao(new DateTime())->setEditor($this->security->getUser());
        } else {
            $login->setCriacao(new DateTime())->setCriador($this->security->getUser());
        }
        
        return $this->_save($login);
    }

    public function select(array $atribs = array(), $orderBy = array(), $page = null, $join = array()) {
        $from = $this->selectFrom;
        $query = $this->repository->createQueryBuilder($from);
        /***********************************************************************************************   JOIN    ***/
        if(!is_array($join))
            $join = array($join);
        foreach ($join as $table) {
            if($table == 'criador_logins') {
                $login_from = 'll';
                $query->innerJoin($from . '.criador', $login_from)->addSelect($login_from);
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
            } else if ($atrib == 'id_in') { // Exemplo de in
                if (is_array($valor) && count($valor))
                    $query->andWhere($from . '.id in (:id_in)')->setParameter('id_in', $valor);
            } else if ($atrib == 'criador') {
                if($valor instanceof Logins)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.criador = :criador')->setParameter('criador', (int)$valor);
            } else if ($atrib == 'email=') {
                if ($valor != '')
                    $query->andWhere($from . '.email = :email')->setParameter('email', $valor);
            } else if ($atrib == 'token_trocar_senha') {
                if ($valor != '')
                    $query->andWhere($from . '.token_trocar_senha = :tokenTrocarSenha')->setParameter('tokenTrocarSenha', $valor);
            } else if ($atrib == 'email_nome') {
                if ($valor != '')
                    $query->andWhere($from . '.email like :email_nome or ' . $from . '.nome like :email_nome')->setParameter('email_nome', TextService::spaceToPercent($valor));
            } else if ($atrib == 'ativo') {
                if (is_numeric($valor))
                    $query->andWhere($from . '.ativo = :ativo')->setParameter('ativo', (int)$valor);
            } else if ($atrib == 'escritorio') {
                if ($valor != 0)
                    $select->where('t.'.$atrib . ' = ?', (int) $valor);
            } else if ($atrib == 'tipo') {
                if ($valor != 0)
                    $select->where('t.'.$atrib . ' = ?', (int) $valor);
            } else if ($atrib == 'data_de') {
                if (Rtl_Data::valida_data($valor, '/'))
                    $select->where('t.data != "" and t.data >= ?', Rtl_Data::converte ($valor, 'en', ''));
            } else if ($atrib == 'data_ate') {
                if (Rtl_Data::valida_data($valor, '/'))
                    $select->where('t.data != "" and t.data <= ?', Rtl_Data::converte ($valor, 'en', ''));
            } else if ($atrib == 'data_agenda_de') {
                if (Rtl_Data::valida_data($valor, '/'))
                    $select->where('t.data_agenda != "" and t.data_agenda >= ?', Rtl_Data::converte ($valor, 'en', ''));
            } else if ($atrib == 'data_agenda_ate') {
                if (Rtl_Data::valida_data($valor, '/'))
                    $select->where('t.data_agenda != "" and t.data_agenda <= ?', Rtl_Data::converte ($valor, 'en', ''));
            } else if ($atrib == 'situacao') {
                if (is_numeric($valor))
                    $select->where('t.'.$atrib . ' = ?', (int) $valor);
            } else if ($atrib == 'atrasados') {
                if ($atrib_valor != 0) {
                    $select->where('t.data_agenda != "" and DATEDIFF(t.data_agenda, NOW()) < 0');
                    $select->where('t.data_agenda is null or t.situacao = 0');
                }
            } else if ($atrib == 'atendimento_id') {
                if ($atrib_valor != 0)
                    $select->where('a.id = ?', (int) $atrib_valor);
            } else if ($atrib == 'processo_id') {
                if ($atrib_valor != 0)
                    $select->where('p.id = ?', (int) $atrib_valor);
            } else if ($atrib == 'atendimento_numero') {
                if ($atrib_valor != '')
                    $select->where('a.numero like ?', $atrib_valor . '%');
            } else if ($atrib == 'processo_numero') {
                if ($atrib_valor != '')
                    $select->where('p.numero like ?', $atrib_valor . '%');
            } else if ($atrib == 'tarefas_responsaveis_responsavel') {
                if ($atrib_valor != 0) {
                    $select->setIntegrityCheck(false);
                    $select->joinLeft(array('tr' => 'tarefas_responsaveis'), 'tr.tarefa = t.id', array('tr.id as tarefa_responsavel_id', 'tr.tarefa as tarefa_responsavel_tarefa'));
                    $select->where('tr.responsavel = ?', (int) $atrib_valor);
                } 
            } else if ($atrib == 'tarefas_responsaveis_responsavel_or_null') {
                if ($atrib_valor != 0) {
                    $select->setIntegrityCheck(false);
                    $select->joinLeft(array('tr' => 'tarefas_responsaveis'), 'tr.tarefa = t.id', array('tr.id as tarefa_responsavel_id', 'tr.tarefa as tarefa_responsavel_tarefa'));
                    $select->where('t.responsaveis is null or tr.responsavel = ?', (int) $atrib_valor);
                } 
            } else if ($atrib == 'atendimento_processo_carteira') {
                if ($atrib_valor != 0) {
                    $select->where('a.carteira = ? or p.carteira = ?', (int) $atrib_valor);
                } 
            } else if ($atrib == 'limit') {
                if (is_numeric($valor))
                    $query->setMaxResults($valor);
            } else {
                if ($valor != '')
                    $query->andWhere('l.' . $atrib . ' like :' . $atrib)->setParameter($atrib, TextService::spaceToPercent($valor));
            }
        }
        /********************************************************************************************   / WHERE    ***/
        return $this->_select($query, $orderBy, $page);
    }
    
    protected function setDependencies($dependencies = array(), $entitys = array()) {
        //Sem dependencias...
        return $this;
    }

    /*************************************************************************************   / MÉTODOS ABSTRATOS   ***/
    
    /******************************************************************************************   REGRA DE NEGÓCIO   ***/
    
    public static function roles($role = null) {
        /*  Níveis de Acesso:
         *  1. Administrador (ROLE_ADMIN)
         *  2. Operacional (ROLE_OPERADOR)
         *  3. Assinante (ROLE_ASSINANTE)
         *  4. Colaborador (ROLE_COLABORADOR)
         *  5. Cliente (ROLE_CLIENTE)
        */
        $roles = array('ROLE_ADMIN' => 'Administrador', 'ROLE_USUARIO' => 'Usuário', 'ROLE_CANDIDATO' => 'Candidato');
        return $role ? $roles[$role] : $roles;
    }

    public static function rolesChoiceArray($firstItem = null) {
        $array = array();
        if ($firstItem)
            $array[$firstItem] = '';
        foreach (LoginsMapper::roles() as $key => $value) {
            $array[$value] = $key;
        }
        return $array;
    }
    
    public function findByEmail($email) {
        return $this->repository->findOneBy(['email' => $email]);
    }
    
    public function findOrCreateLogin($login_data, $role = null, $prefixo = '') {
        if(!$login = $this->findByEmail($login_data[$prefixo . 'email'])) {
            $login = new Logins();
            // id, criacao, criador, edicao, editor, email, pass, nome, role, hash, hash_criacao, confirmado, ativo, 
            $login->setEmail($login_data[$prefixo . 'email'])
                    ->setPass($login_data[$prefixo . 'pass'])
                    ->setNome($login_data[$prefixo . 'nome'])
                    ->setRole($role)
                    ->setConfirmado(false)
                    ->setAtivo(true);
            $login = $this->save($login, $login);
            /*
            if(($login = $this->save($login, $login)) instanceof Logins) {
                if($login_data[$prefixo . 'pass']) {
                    $loginsMapper->savePass ($login->get('id'), $login_data[$prefixo . 'pass']);
                }
            }
            */
        }
        return $login;
    }
    
    public function savePass( $login, $pass, $confirmado = false) {
        // id, criacao, criador, edicao, editor, email, pass, nome, role, hash, hash_criacao, confirmado, ativo, 
        $this->checkEntityClass($login, true);
        if(!$pass)
            return ['O Campo Senha não pode estar vazio'];
        $login->setPass($this->passwordEncoder->encodePassword($login, $pass))
                ->setTokenTrocarSenha(null)
                ->setDataGeracaoToken(null)
                ->setHash(null)
                ->setHashCriacao(null);

        if($confirmado)
            $login->setConfirmado(true);
        
        return $this->_save($login);
    }

    public function saveTokenTrocarSenha($login, $token){

        $this->checkEntityClass($login, true);
        $login->setTokenTrocarSenha($token)
                ->setDataGeracaoToken(new DateTime())
                ;

        return $this->_save($login);
    }

    
    /* REAVALIAR DEPOIS
    public function savePassAcesso($old, $data) {
        if(!($old instanceof Application_Model_Login))
            exit('Application Error:<br/>Application_Model_LoginsMapper->savePassAcesso(): Instância Inválida');
        if ($data instanceof Application_Model_Login)
            $data = $data->toArray();
        else if ($data instanceof Zend_Form) {
            $data = $data->getValues();
        } else if (gettype($data) != 'array') {
            exit('Application Error:<br/>Application_Model_LoginsMapper->savePassAcesso(): Tipo Inválido');
        }
        $errors = array(); // VETOR DE ERROS: $errors[]='______';
        
        if(!$data['id'] = $old->get('id')) $errors[] = 'Login não identificado';
        if (sha1($data['old_pass']) !== Rtl_Login::get('pass')) {
            $errors[] = 'Senha incorreta';
        }
        $data['pass'] = sha1($data['pass']);
        unset($data['old_pass'], $data['confirm_pass']);
        if(count($errors)) {
            array_unshift($errors, 'Erros foram encontrados');
            return $errors;
        }
        $data['id'] = $this->_save($data);
        return new Application_Model_Login($data);
    }
    */
    /* REAVALIAR DEPOIS
    public function saveNomeAcesso($old, $data) {
        if(!($old instanceof Application_Model_Login))
            exit('Application Error:<br/>Application_Model_LoginsMapper->saveNomeAcesso(): Instância Inválida');
        if ($data instanceof Application_Model_Login)
            $data = $data->toArray();
        else if ($data instanceof Zend_Form) {
            $data = $data->getValues();
        } else if (gettype($data) != 'array') {
            exit('Application Error:<br/>Application_Model_LoginsMapper->saveNomeAcesso(): Tipo Inválido');
        }
        $errors = array(); // VETOR DE ERROS: $errors[]='______';
        
        if(!$data['id'] = $old->get('id')) $errors[] = 'Login não identificado';
        if (!$data['nome']) {
            $errors[] = 'O campo "Nome" não pode estar vazio';
        }
        if(count($errors)) {
            array_unshift($errors, 'Erros foram encontrados');
            return $errors;
        }
        $data['id'] = $this->_save($data);
        return new Application_Model_Login($data);
    }
    */
    /* REAVALIAR DEPOIS
    public function saveEmailAcesso($old, $data) {
        if(!($old instanceof Application_Model_Login))
            exit('Application Error:<br/>Application_Model_LoginsMapper->saveNomeAcesso(): Instância Inválida');
        if ($data instanceof Application_Model_Login)
            $data = $data->toArray();
        else if ($data instanceof Zend_Form) {
            $data = $data->getValues();
        } else if (gettype($data) != 'array') {
            exit('Application Error:<br/>Application_Model_LoginsMapper->saveNomeAcesso(): Tipo Inválido');
        }
        $errors = array(); // VETOR DE ERROS: $errors[]='______';
        
        if(!$data['id'] = $old->get('id')) $errors[] = 'Login não identificado';
        if (!$data['email']) {
            $errors[] = 'O campo "Novo E-mail" não pode estar vazio';
        }
        if (sha1($data['pass']) !== Rtl_Login::get('pass')) {
            $errors[] = 'Senha incorreta';
        }
        
        if($this->findByEmail($data['email'])) {
            $errors[] = 'E-mail já cadastrado no sistema. Por favor, informe outro e-mail.';
            $errors[] = 'Caso você seja o dono do e-mail, acesse o sistema com ele, recupere sua senha ou entre em contato com o suporte.';
        }
        
        $data['hash'] = sha1(rand(0, 10000) . date('Y-m-y') . 'hash');
        $data['confirmado'] = 0;
        $data['pass'] = rand(0, 10000) . date('Y-m-y') . 'senha';
        unset($data['email_confirmacao']);
        
        if(count($errors)) {
            array_unshift($errors, 'Erros foram encontrados');
            return $errors;
        }
        $data['id'] = $this->_save($data);
        return new Application_Model_Login($data);
    }
    */
    /* REAVALIAR DEPOIS
    */
    
    /* REAVALIAR DEPOIS
    public static function setRules() {
        /*  Níveis de Acesso
         *  1. Administrador
         *  2. Operacional
         *  3. Assinante
         *  4. Colaborador
         *  5. Cliente
        *
        
        Rtl_Login::addRole(0);
        Rtl_Login::allowController(0, 'login');
        Rtl_Login::addRole(1);
        Rtl_Login::allow(1);
        Rtl_Login::addRole(2);
        Rtl_Login::allow(2);
        Rtl_Login::addRole(3);
        Rtl_Login::allowModule(3, 'default');
        Rtl_Login::addRole(4);
        Rtl_Login::allowModule(4, 'default');
        //Rtl_Login::addRole(5);
        //Rtl_Login::allowController(5, array('acesso', 'login'));
        //Rtl_Login::allowController(5, array('mensagens', 'login'));
        //Rtl_Login::denyAction(5, array('alterar-servidores'), 'acesso');
        //Rtl_Login::allowModule(5, 'fornecedor');
    }
    */
    /* REAVALIAR DEPOIS
    /* REAVALIAR DEPOIS
    public static function afterLoginPage() {
        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        $loginsMapper = new Application_Model_LoginsMapper();
        $login = $loginsMapper->find(Rtl_Login::get('id'));
        if(Rtl_Login::is(1, 2)) {
            Rtl_Session::set('login', $login);
            return $baseUrl . '/admin';
        } else if(Rtl_Login::is(3)) {
            if(!$login->assinante()) {
                Rtl_SMessages::setMessages(array('Não foi possível associar seu acesso a nenhum assinante.', 'Por favor, entre em contato com a administração do Aguiar Sistema'),3);
                Rtl_Login::logout();
                return $baseUrl . '/login/index';
            }
            if($colaborador = Application_Model_LoginsMapper::getColaboradorPadrao($login)) {
                Rtl_Session::set('colaborador', $colaborador);
                Rtl_Session::set('escritorio', $colaborador->escritorio());
            }
            Rtl_Session::set('login', $login);
            return $baseUrl;
        }  else if(Rtl_Login::is(4)) {
            if($colaborador = Application_Model_LoginsMapper::getColaboradorPadrao($login)) {
                Rtl_Session::set('colaborador', $colaborador);
                Rtl_Session::set('escritorio', $colaborador->escritorio());
                Rtl_Session::set('login', $login);
                return $baseUrl;
            }
            Rtl_SMessages::setMessages(array('Não foi possível associar seu acesso a nenhum escritório.', 'Por favor, entre em contato com a administração do Aguiar Sistema'),3);
            Rtl_Login::logout();
            return $baseUrl . '/login/index';
        } /*else if(Rtl_Login::is(5)) {
            $fornecedoresMapper = new Application_Model_FornecedoresMapper();
            if(count($result = $fornecedoresMapper->filter(array('login' => Rtl_Login::get('id'))))){
                Rtl_Session::set('fornecedor', $result[0]);
                Rtl_Session::set('login', $login);
                return $baseUrl . '/fornecedor';
            }
            Rtl_SMessages::setMessage('Não foi possível identificar seus dados de fornecedor',3);
            return $baseUrl . '/login/index';
        } *
    }
    */
    /* REAVALIAR DEPOIS
    public static function getColaboradorPadrao($login) {
        if(!($login instanceof Application_Model_Login) || !$login->get('id'))
            exit ('Não foi possivel identificar o login');
        if(count($result = $login->colaboradores())) {
            return $result[0];
        }
        return null;
    }
    */

    /****************************************************************************************   / REGRA DE NEGÓCIO   ***/
    
}


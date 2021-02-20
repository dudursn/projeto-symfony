<?php

namespace App\Mapper;

use App\Entity\Cidades;
use App\Entity\Usuarios;
use App\Entity\Lojas;
use App\Entity\Ufs;
use App\Mapper\AbstractMapper;
use App\Services\FilesService;
use App\Services\TextService;
use DateTime;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UsuariosMapper extends AbstractMapper
{
    // 'id', 'criacao', 'criador', 'criador_colaborador', 'edicao', 'editor', 'editor_colaborador', 'loja', 'nome', 'tipo', 'doc', 'role', 'identificacao', 'cargo', 'rg', 'matricula', 'nascimento', 'sexo', 'nit', 'titulo_eleitor', 'estado_civil', 'ctps', 'mae_nome', 'pai_nome', 'razao_social', 'insc_esta', 'insc_muni', 'genero', 'contato_nome', 'contato_cargo', 'contato_email', 'contato_tel', 'dados_bancarios', 'logradouro', 'enumero', 'complemento', 'bairro', 'uf', 'cidade', 'cep', 'tel1', 'tel1_obs', 'tel2', 'tel2_obs', 'tel3', 'tel3_obs', 'tel4', 'tel4_obs', 'email', 'obs', 'imagem', 'comissao_valor', 'comissao_percentagem'
    protected $entityClass = Usuarios::class;
    protected $selectFrom = 'c';
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($usuario, $data) { // Usuarios 
        $this->checkEntityClass($usuario);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$data->getLogin())
            $errors[] = 'O campo Login não pode estar vazio';
        else if($data->getLogin()->getRole() != 'ROLE_USUARIO') {
            $errors[] = 'Login não permitido para associar ao Usuário. Por favor, selecione outro Login';
        }
        
        if(!$data->getNome())
            $errors[] = 'O campo Nome não pode estar vazio';
        if(!$data->getDoc())
            $errors[] = 'O campo CPF não pode estar vazio';
        if(count($this->select(['doc='=>$data->getDoc(), 'id!='=>$usuario->getId()])))
            $errors[] = 'Um Usuário com o mesmo CPF já foi cadastrado. Por favor, informe outro CPF/CNPJ para este Usuário';
        
        $data->setSexo((int)$data->getSexo()?$data->getSexo():null);
        $data->setEmail($data->getEmail()?$data->getEmail():$data->getLogin()->getEmail());
        
        if(count($errors)) {
            return $errors;
        }
        
        $usuario->setLogin($data->getLogin())
                ->setNome($data->getNome())
                ->setDoc($data->getDoc())
                ->setWhatsapp($data->getWhatsapp())
                ->setTelefone($data->getTelefone())
                ->setEmail($data->getEmail())
                ->setCargo($data->getCargo())
                ->setRg($data->getRg())
                ->setMatricula($data->getMatricula())
                ->setNascimento($data->getNascimento())
                ->setSexo($data->getSexo())
                ->setCep($data->getCep())
                ->setLogradouro($data->getLogradouro())
                ->setEnumero($data->getEnumero())
                ->setComplemento($data->getComplemento())
                ->setBairro($data->getBairro())
                ->setUf($data->getUf())
                ->setCidade($data->getCidade())
                ;
        
        if($usuario->getId()) {
            $usuario->setEdicao(new DateTime())->setEditor($this->security->getUser());
        } else {
            $usuario->setCriacao(new DateTime())->setCriador($this->security->isGranted('ROLE_USER')?$this->security->getUser():$usuario->getLogin());
        }
        
        return $this->_save($usuario);
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
            } else if ($atrib == 'nome') {
                if ($valor != '')
                    $query->andWhere($from . '.nome = :nome')->setParameter('nome', $valor);
            } else if ($atrib == 'nome_identificacao') {
                if ($valor != '')
                    $query->andWhere($from . '.nome like :nome_identificacao or ' . $from . '.identificacao like :nome_identificacao')->setParameter('nome_identificacao', TextService::spaceToPercent($valor));
            } else if ($atrib == 'doc=') {
                if ($valor != '')
                    $query->andWhere($from . '.doc = :doc')->setParameter('doc', $valor);
            } else if ($atrib == 'ativo') {
                if (is_numeric($valor))
                    $query->andWhere($from . '.ativo = :ativo')->setParameter('ativo', (int)$valor);
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
    
    public function saveRegistro($form, LoginsMapper $loginsMapper, \App\Mapper\CpfsPermitidosMapper $cpfsPermitidosMapper) {
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        $data = $form->getData();
        
        // Checa se Usuário já está cadastrado pelo CPF
        $usuarios = $this->select(['doc='=>$data['doc']]);
        if(count($usuarios)) {
            $errors[] = 'Usuário já cadastrado na Plataforma. Por favor, entre no sistema com seu e-mail <strong>' . $usuarios[0]->getLogin()->getEmail() . '</strong> e sua senha.';
        }
        // Checa se o CPF é Permitido
        $cpfsPermitidos = $cpfsPermitidosMapper->select(['cpf='=>$data['doc']]);
        if(!count($cpfsPermitidos)) {
            $errors[] = 'Você não tem permissão para acessar esta plataforma de votos. Por favor, tente acessar com outro CPF.';
        }
        // Checa se Login já está cadastrado pelo E-mail
        $logins = $loginsMapper->select(['email='=>$data['email']]);
        if(count($logins)) {
            $errors[] = 'E-mail já cadastrado na Plataforma. Por favor, entre no sistema com seu e-mail <strong>' . $logins[0]->getEmail() . '</strong> e sua senha ou tente registrar com outro e-mail.';
        }
        
        if(count($errors)) {
            return $errors;
        }
        
        // Cria o Login
        $login = new \App\Entity\Logins();
        $_login = clone $login;
        $_login->setEmail($data['email'])
                ->setPass($data['pass'])
                ->setNome($data['nome'])
                ->setRole('ROLE_USUARIO')
                ->setConfirmado(false)
                ->setAtivo(true)
                ->setTokenTrocarSenha("")
                ->setDataGeracaoToken("")
                ;
        
        if(!(($login = $loginsMapper->save($login, $_login)) instanceof \App\Entity\Logins)) {
            return $login;
        }
        
        // Cria o Usuario
        $usuario = new \App\Entity\Usuarios();
        $_usuario = clone $usuario;
        $_usuario->setLogin($login)
                ->setNome($data['nome'])
                ->setDoc($data['doc'])
                ->setNascimento($data['nascimento'])
                ->setWhatsapp($data['whatsapp'])
                ->setTelefone($data['telefone'])
                ;
     
                
        
        if(!(($usuario = $this->save($usuario, $_usuario)) instanceof \App\Entity\Usuarios)) {
            $this->flash('Um erro aconteceu no registro. Por favor, entre em contato com o suporte para efetuar o cadastro manualmente', 'd');
            return $usuario;
        }
        
        return $usuario;
    }    
    
    public function getLoginUsuario(?\App\Entity\Logins $login = null) {
        if(!($login instanceof Logins))
            $login = $this->security->getUser();
        if(count($usuarios = $this->select(['login' => $login]))){
            return $usuarios[0];
        }
        return null;
    }
    
    /****************************************************************************************   / REGRA DE NEGÓCIO   ***/
    
}


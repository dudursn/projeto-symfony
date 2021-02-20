<?php

namespace App\Mapper;

use App\Entity\Assinantes;
use App\Entity\Cidades;
use App\Entity\Ufs;
use App\Mapper\AbstractMapper;
use App\Services\TextService;
use DateTime;

class AssinantesMapper extends AbstractMapper
{
    // id, criacao, criador, edicao, editor, login, nome, tipo, doc, rg, nascimento, sexo, razao_social, insc_esta, insc_muni, contato_nome, contato_cargo, contato_email, contato_tel, logradouro, enumero, complemento, bairro, uf, cidade, cep, tel1, tel2, tel3, tel4, email, obs
    protected $entityClass = Assinantes::class;
    protected $selectFrom = 'a';
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($assinante, $data) { // Assinantes 
        $this->checkEntityClass($assinante);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$assinante->getLogin())
            $errors[] = 'Login não identificado';
        if(!$data->getNome())
            $errors[] = 'O campo Nome não pode estar vazio';
        if($data->getDoc()) {
            if(count($this->select(['doc='=>$data->getDoc(), 'id!='=>$assinante->getId()])))
                $errors[] = 'Um Assinante com o mesmo CPF/CNPJ já foi cadastrado. Por favor, informe outro CPF/CNPJ para este Assinante';
        } else {
            if(count($this->select(['nome='=>$data->getNome(), 'id!='=>$assinante->getId()])))
                $errors[] = 'Um Assinante com o mesmo NOME já foi cadastrado. Por favor, informe outro Nome para este Assinante';
        }
        if($data->view('tipo_cpf?')) {
            $data->setSexo((int)$data->getSexo()?$data->getSexo():null);
            $data->setRazaoSocial(null)->setInscEsta(null)->setInscMuni(null)->setContatoNome(null)->setContatoCargo(null)->setContatoEmail(null)->setContatoTel(null);
        } else if($data->view('tipo_cnpj?')) {
            $data->setRg(null)->setNascimento(null)->setSexo(null);
        } else {
            $errors[] = 'Tipo NÃO definido';
        }
        if($data->getCidade()) {
            $data->setUf($data->getCidade()->getUf());
        }
        
        if(count($errors)) {
            return $errors;
        }
        
        $assinante->setNome($data->getNome())
                ->setTipo($data->getTipo())
                ->setDoc($data->getDoc())
                ->setRg($data->getRg())
                ->setNascimento($data->getNascimento())
                ->setSexo($data->getSexo())
                ->setRazaoSocial($data->getRazaoSocial())
                ->setInscEsta($data->getInscEsta())
                ->setInscMuni($data->getInscMuni())
                ->setContatoNome($data->getContatoNome())
                ->setContatoCargo($data->getContatoCargo())
                ->setContatoEmail($data->getContatoEmail())
                ->setContatoTel($data->getContatoTel())
                ->setLogradouro($data->getLogradouro())
                ->setEnumero($data->getEnumero())
                ->setComplemento($data->getComplemento())
                ->setBairro($data->getBairro())
                ->setUf($data->getUf())
                ->setCidade($data->getCidade())
                ->setCep($data->getCep())
                ->setTel1($data->getTel1())
                ->setTel2($data->getTel2())
                ->setTel3($data->getTel3())
                ->setTel4($data->getTel4())
                ->setEmail($data->getEmail())
                ->setObs($data->getObs())
                ;
        
        if($assinante->getId()) {
            $assinante->setEdicao(new DateTime())->setEditor($this->security->getUser());
        } else {
            $assinante->setCriacao(new DateTime())->setCriador($this->security->getUser());
        }
        
        return $this->_save($assinante);
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
            } else if ($atrib == 'nome=') {
                if ($valor != '')
                    $query->andWhere($from . '.nome = :nome')->setParameter('nome', $valor);
            } else if ($atrib == 'doc=') {
                if ($valor != '')
                    $query->andWhere($from . '.doc = :doc')->setParameter('doc', $valor);
            } else if ($atrib == 'ativo') {
                if (is_numeric($valor))
                    $query->andWhere($from . '.ativo = :ativo')->setParameter('ativo', (int)$valor);
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
        /*
        if(!is_array($dependencies) && $dependencies)
            $dependencies = array($dependencies);
        else if(!$dependencies)
            $dependencies = array();
        foreach ($dependencies as $dependency) {
            if($dependency instanceof NovosToposMapper)
                $this->novosMapper = $dependency;
        }
        
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
    
    public function findByLogin($loginId) {
        if(!$loginId)
            return null;
        return $this->repository->findOneBy(['login' => $loginId]);
    }

    public static function tipos($value = null) {
        /*  Values: 1. Pessoa Física, 2. Pessoa Jurídica */
        $values = array(1 => 'Pessoa Física', 2 => 'Pessoa Jurídica');
        return is_numeric($value) && $value ? $values[$value] : $values;
    }

    public static function tiposChoiceArray($firstItem = null) {
        $array = array();
        if ($firstItem)
            $array[$firstItem] = '';
        foreach (AssinantesMapper::tipos() as $key => $value) {
            $array[$value] = $key;
        }
        return $array;
    }
    
    public static function sexos($value = null) {
        /***   Values: 0. Ignorado, 1. Masculino, 2. Feminino   ***/
        $values = array(0 => 'Ignorado', 1 => 'Masculino', 2 => 'Feminino');
        return is_numeric($value) && $value ? $values[$value] : $values;
    }

    public static function sexosChoiceArray($firstItem = null) {
        $array = array();
        if ($firstItem)
            $array[$firstItem] = '';
        foreach (AssinantesMapper::sexos() as $key => $value) {
            $array[$value] = $key;
        }
        return $array;
    }
    
    /****************************************************************************************   / REGRA DE NEGÓCIO   ***/
    
}


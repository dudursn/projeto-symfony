<?php

namespace App\Mapper;

use App\Entity\Assinantes;
use App\Entity\Cidades;
use App\Entity\Lojas;
use App\Entity\Ufs;
use App\Mapper\AbstractMapper;
use App\Services\FilesService;
use App\Services\TextService;
use DateTime;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LojasMapper extends AbstractMapper
{
    // 'id', 'criacao', 'criador', 'edicao', 'editor', 'assinante', 'loja', 'ramo', 'nome', 'tipo', 'doc', 'rg', 'nascimento', 'sexo', 'razao_social', 'insc_esta', 'insc_muni', 'genero', 'contato_nome', 'contato_cargo', 'contato_email', 'contato_tel', 'logradouro', 'enumero', 'complemento', 'bairro', 'uf', 'cidade', 'cep', 'tel1', 'tel2', 'tel3', 'tel4', 'email', 'obs', 'logo', 'logo_escuro', 'recurso_nf', 'recurso_pagamentos_virtuais', 'recurso_boleto', 'recurso_arquivos', 'nfe', 'nfce', 'nfse', 'nf_ambiente', 'nfe_ultimo_numero', 'nfce_ultimo_numero', 'nfse_ultimo_numero', 'nfe_serie', 'nfce_serie', 'nfse_token', 'nfse_usuario', 'nfse_senha', 'nfse_natureza_operacao', 'nf_natureza_operacao', 'nf_modalidade_frete', 'nf_informacoes_adicionais_contribuinte'
    protected $entityClass = Lojas::class;
    protected $selectFrom = 'l';
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($loja, $data) { // Lojas 
        $this->checkEntityClass($loja);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$loja->getAssinante())
            $errors[] = 'Assinante não identificado';
        if($loja->getLoja()) {
            if((int)$loja->getAssinante()->getId() !== (int)$loja->getLoja()->getAssinante()->getId() ) {
                $errors[] = 'Loja e Filial possuem Assinantes diferentes...';
            }
        }
        if(!$data->getRamo())
            $errors[] = 'O campo Ramo não pode estar vazio';
        if(!$data->getNome())
            $errors[] = 'O campo Nome não pode estar vazio';
        if($data->getDoc()) {
            if(count($this->select(['doc='=>$data->getDoc(), 'id!='=>$loja->getId()])))
                $errors[] = 'Uma Loja com o mesmo CPF/CNPJ já foi cadastrada. Por favor, informe outro CPF/CNPJ para esta Loja';
        } else {
            if(count($this->select(['nome='=>$data->getNome(), 'id!='=>$loja->getId()])))
                $errors[] = 'Uma Loja com o mesmo NOME já foi cadastrada. Por favor, informe outro Nome para esta Loja';
        }
        
        if($data->view('tipo_cpf?')) {
            $data->setSexo((int)$data->getSexo()?$data->getSexo():null);
            $data->setRazaoSocial(null)->setInscEsta(null)->setInscMuni(null)->setGenero(null)->setContatoNome(null)->setContatoCargo(null)->setContatoEmail(null)->setContatoTel(null);
        } else if($data->view('tipo_cnpj?')) {
            $data->setGenero((int)$data->getGenero()?$data->getGenero():null);
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
        
        $loja->setRamo($data->getRamo())
                ->setTipo($data->getTipo())
                ->setNome($data->getNome())
                ->setDoc($data->getDoc())
                ->setRg($data->getRg())
                ->setNascimento($data->getNascimento())
                ->setSexo($data->getSexo())
                ->setRazaoSocial($data->getRazaoSocial())
                ->setInscEsta($data->getInscEsta())
                ->setInscMuni($data->getInscMuni())
                ->setGenero($data->getGenero())
                ->setContatoNome($data->getContatoNome())
                ->setContatoCargo($data->getContatoCargo())
                ->setContatoEmail($data->getContatoEmail())
                ->setContatoTel($data->getContatoTel())
                ->setCep($data->getCep())
                ->setLogradouro($data->getLogradouro())
                ->setEnumero($data->getEnumero())
                ->setComplemento($data->getComplemento())
                ->setBairro($data->getBairro())
                ->setUf($data->getUf())
                ->setCidade($data->getCidade())
                ->setTel1($data->getTel1())
                ->setTel2($data->getTel2())
                ->setTel3($data->getTel3())
                ->setTel4($data->getTel4())
                ->setEmail($data->getEmail())
                ->setObs($data->getObs())
                ->setRecursoNf($data->getRecursoNf())
                ->setRecursoPagamentosVirtuais($data->getRecursoPagamentosVirtuais())
                ->setRecursoBoleto($data->getRecursoBoleto())
                ->setRecursoArquivos($data->getRecursoArquivos())
                ->setPedidosSemCliente($data->getPedidosSemCliente())
                ->setPedidosNumeracao($data->getPedidosNumeracao())
                ->setPedidosNumeracaoInicial($data->getPedidosNumeracaoInicial())
                ->setCotacoesSemCliente($data->getCotacoesSemCliente())
                ->setCotacoesNumeracao($data->getCotacoesNumeracao())
                ->setCotacoesNumeracaoInicial($data->getCotacoesNumeracaoInicial())
                ;
        
        if($loja->getId()) {
            $loja->setEdicao(new DateTime())->setEditor($this->security->getUser());
        } else {
            $loja->setCriacao(new DateTime())->setCriador($this->security->getUser())
                    ->setPedidosNumeracao('sequencia')
                    ->setCotacoesNumeracao('sequencia');
        }
        
        return $this->_save($loja);
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
            } else if ($atrib == 'assinante') {
                if($valor instanceof Assinantes)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.assinante = :assinante')->setParameter('assinante', (int)$valor);
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
    
    public function saveImagens(Lojas $loja, ?UploadedFile $logo, ?UploadedFile $logoEscuro) {
        $this->checkEntityClass($loja, true);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        $logoPath = '';
        $logoEscuroPath = '';
        if($logo) {
            if(!$this->isAllowedFileType($logo, 'imagens'))
                $errors[] = 'Tipo de arquivo não permitido (' . $logo->getMimeType() . ')';
            $originalName = pathinfo($logo->getClientOriginalName(), PATHINFO_FILENAME);
            $ext = $logo->getClientOriginalExtension()?$logo->getClientOriginalExtension():($logo->guessExtension()?$logo->guessExtension():'tmp');
            $fileName = FilesService::getFileDatePrefix() . 'loja_' . $loja->getId() . '_logo.' . $ext;
            if(!$logo->move(FilesService::getDatePath($this->getPublicFilesPath()), $fileName))
                die('Não foi possível mover o arquivo (' . $fileName . '[' . $originalName . '])');
            $logoPath = FilesService::getDatePath($this->getPublicFilesPath(), true) . '/' . $fileName;
        }
        
        if($logoEscuro) {
            if(!$this->isAllowedFileType($logoEscuro, 'imagens'))
                $errors[] = 'Tipo de arquivo não permitido (' . $logoEscuro->getMimeType() . ')';
            $originalName = pathinfo($logoEscuro->getClientOriginalName(), PATHINFO_FILENAME);
            $ext = $logoEscuro->getClientOriginalExtension()?$logoEscuro->getClientOriginalExtension():($logoEscuro->guessExtension()?$logoEscuro->guessExtension():'tmp');
            $fileName = FilesService::getFileDatePrefix() . 'loja_' . $loja->getId() . '_logo_escuro.' . $ext;
            if(!$logoEscuro->move(FilesService::getDatePath($this->getPublicFilesPath()), $fileName))
                die('Não foi possível mover o arquivo (' . $fileName . '[' . $originalName . '])');
            $logoEscuroPath = FilesService::getDatePath($this->getPublicFilesPath(), true) . '/' . $fileName;
        }
        
        if(!$logo && !$logoEscuro)
            $errors[] = 'Nenhuma imagem selecionada. Por favor, selecione as imagens para a loja.';
        
        if(count($errors)) {
            if($logoPath)
                FilesService::removeFile($logoPath, $this->getPublicFilesPath());
            if($logoEscuroPath)
                FilesService::removeFile($logoEscuroPath, $this->getPublicFilesPath());
            return $errors;
        }
        
        if($logo) {
            if($loja->getLogo())
                FilesService::removeFile($loja->getLogo(), $this->getPublicFilesPath());
            $loja->setLogo($logoPath);
        }
        if($logoEscuro) {
            if($loja->getLogoEscuro())
                FilesService::removeFile($loja->getLogoEscuro(), $this->getPublicFilesPath());
            $loja->setLogoEscuro($logoEscuroPath);
        }
        
        return $this->_save($loja);
    }

    public function deleteImagem(Lojas $loja, $logo) {
        if($logo == 'logo') {
            FilesService::removeFile($loja->getLogo(), $this->getPublicFilesPath());
            $loja->setLogo(null);
        } else if($logo == 'logo_escuro') {
            FilesService::removeFile($loja->getLogoEscuro(), $this->getPublicFilesPath());
            $loja->setLogoEscuro(null);
        } else {
            return false;
        }
        return $this->_save($loja);
    }

    public function findByLogin($loginId) {
        if(!$loginId)
            return null;
        return $this->repository->findOneBy(['login' => $loginId]);
    }

    public static function ramos($value = null) {
        /** Values:
         * 1. Pessoal
         * 2. Comércio
         * 3. Prestadora de Serviços
         * 4. Ótica
         * 5. Restaurante
         **/
        
        $values = [1 => 'Pessoal', 2 => 'Comércio', 3 => 'Prestadora de Serviços', 4 => 'Ótica', 5 => 'Restaurante'];
        return is_numeric($value) && $value ? $values[$value] : $values;
    }

    public static function ramosChoiceArray($firstItem = null) {
        $array = [];
        if ($firstItem)
            $array[$firstItem] = '';
        foreach (LojasMapper::ramos() as $key => $value)
            $array[$value] = $key;
        
        return $array;
    }
    
    public function isAllowedFileType(?File $file, $escopo = null)
    {
        if(in_array($file->getMimeType(), \App\Services\Constants::arquivosMimeTypes($escopo)))
            return true;
        return false;
    }
    
    /****************************************************************************************   / REGRA DE NEGÓCIO   ***/
    
}


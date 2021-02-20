<?php

namespace App\Mapper;

use App\Entity\Categorias;
use App\Entity\Cidades;
use App\Entity\Colaboradores;
use App\Entity\Lojas;
use App\Entity\Pessoas;
use App\Entity\Ufs;
use App\Mapper\AbstractMapper;
use App\Services\Constants;
use App\Services\FilesService;
use App\Services\TextService;
use DateTime;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PessoasMapper extends AbstractMapper
{
    // 'id', 'criacao', 'criador', 'criador_colaborador', 'edicao', 'editor', 'editor_colaborador', 'loja', 'tipo', 'nome', 'doc', 'rg', 'apelido', 'nascimento', 'sexo', 'nit', 'titulo_eleitor', 'ctps', 'mae_nome', ' pai_nome', ' nacionalidade', ' profissao', ' estado_civil', ' escolaridade', ' razao_social', 'insc_esta', 'insc_muni', 'genero', ' logradouro', 'enumero', 'complemento', 'bairro', 'uf', 'cidade', 'cep', 'tel1', 'tel1_obs', 'tel2', 'tel2_obs', 'tel3', 'tel3_obs', 'tel4', 'tel4_obs', 'email', 'comissionado', 'obs', 'imagem', ' role_cliente', 'role_fornecedor', 'role_oftalmologista', 'role_transportadora', 'role_laboratorio', 'cobranca', 'cobranca_ultima_cobranca', 'cobranca_proxima_cobranca', 'cobranca_obs', 'otica_dp', 'otica_dnp_od', 'otica_dnp_oe', 'otica_dp_perto', 'otica_dnp_perto_od', 'otica_dnp_perto_oe'
    protected $entityClass = Pessoas::class;
    protected $selectFrom = 'p';
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($pessoa, $data) { // Pessoas 
        $this->checkEntityClass($pessoa);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$pessoa->getLoja())
            $errors[] = 'Loja não identificada';
        if(!$data->getNome())
            $errors[] = 'O campo Nome/Razão Social não pode estar vazio';
        if(!((int)$data->getTipo() === 1 || (int)$data->getTipo() === 2))
            $errors[] = 'O campo Tipo possui um valor inválido';
        if($data->getDoc()) {
            if(count($this->select(['loja'=>$data->getLoja(), 'doc='=>$data->getDoc(), 'id!='=>$data->getId()])))
                $errors[] = 'Uma Pessoa com o mesmo CPF/CNPJ já foi cadastrada. Por favor, informe outro CPF/CNPJ para esta Pessoa';
        } else {
            if(count($this->select(['loja'=>$data->getLoja(), 'nome='=>$data->getNome(), 'id!='=>$data->getId()])))
                $errors[] = 'Uma Pessoa com o mesmo Nome já foi cadastrada. Por favor, informe outro Nome para esta Pessoa';
        }
        /****************************************************************************************   VALIDANDO D.P.   */
        /**************************************************************************************   / VALIDANDO D.P.   */
        if($data->getOticaDp() < 0 || $data->getOticaDp() > 99)
            $errors[] = 'O valor do campo D.P. não pode ser menor que 1 ou maior que 99';
        if($data->getOticaDpPerto() < 0 || $data->getOticaDpPerto() > 99)
            $errors[] = 'O valor do campo DP Perto não pode ser menor que 1 ou maior que 99';
        if($data->getOticaDnpOd() < 0 || $data->getOticaDnpOd() > 99)
            $errors[] = 'O valor do campo DNP OD não pode ser menor que 1 ou maior que 99';
        if($data->getOticaDnpOe() < 0 || $data->getOticaDnpOe() > 99)
            $errors[] = 'O valor do campo DNP OE não pode ser menor que 1 ou maior que 99';
        if($data->getOticaDnpPertoOd() < 0 || $data->getOticaDnpPertoOd() > 99)
            $errors[] = 'O valor do campo DPN Perto OD não pode ser menor que 1 ou maior que 99';
        if($data->getOticaDnpPertoOe() < 0 || $data->getOticaDnpPertoOe() > 99)
            $errors[] = 'O valor do campo DPN Perto OE não pode ser menor que 1 ou maior que 99';
        /**************************************************************************************   / VALIDANDO D.P.   */
        
        if(count($errors)) {            
            return $errors;
        }
        
        if($data->getCidade()) {
            $data->setUf($data->getCidade()->getUf());
        }
        
        $pessoa->setTipo($data->getTipo())
                ->setNome($data->getNome())
                ->setDoc($data->getDoc())
                ->setRg($data->getRg())
                ->setNascimento($data->getNascimento())
                ->setSexo($data->getSexo())
                ->setRazaoSocial($data->getRazaoSocial())
                ->setInscEsta($data->getInscEsta())
                ->setInscMuni($data->getInscMuni())
                ->setGenero($data->getGenero())
                ->setTel1($data->getTel1())
                ->setTel1Obs($data->getTel1Obs())
                ->setTel2($data->getTel2())
                ->setTel2Obs($data->getTel2Obs())
                ->setTel3($data->getTel3())
                ->setTel3Obs($data->getTel3Obs())
                ->setTel4($data->getTel4())
                ->setTel4Obs($data->getTel3Obs())
                ->setEmail($data->getEmail())
                ->setCep($data->getCep())
                ->setLogradouro($data->getLogradouro())
                ->setEnumero($data->getEnumero())
                ->setComplemento($data->getComplemento())
                ->setBairro($data->getBairro())
                ->setUf($data->getUf())
                ->setCidade($data->getCidade())
                ->setComissionado($data->getComissionado())
                ->setCategoria($data->getCategoria())
                ->setRoleCliente($data->getRoleCliente())
                ->setRoleFornecedor($data->getRoleFornecedor())
                ->setRoleTransportadora($data->getRoleTransportadora())
                ->setRoleLaboratorio($data->getRoleLaboratorio())
                ->setRoleOftalmologista($data->getRoleOftalmologista())
                ->setApelido($data->getApelido())
                ->setAposentado($data->getAposentado())
                ->setProfissao($data->getProfissao())
                ->setNacionalidade($data->getNacionalidade())
                ->setEscolaridade($data->getEscolaridade())
                ->setEstadoCivil($data->getEstadoCivil())
                ->setNit($data->getNit())
                ->setTituloEleitor($data->getTituloEleitor())
                ->setCtps($data->getCtps())
                ->setMaeNome($data->getMaeNome())
                ->setPaiNome($data->getPaiNome())
                ->setObs($data->getObs())
                ->setOticaDp($data->getOticaDp())
                ->setOticaDpPerto($data->getOticaDpPerto())
                ->setOticaDnpOd($data->getOticaDnpOd())
                ->setOticaDnpOe($data->getOticaDnpOe())
                ->setOticaDnpPertoOd($data->getOticaDnpPertoOd())
                ->setOticaDnpPertoOe($data->getOticaDnpPertoOe())
                ;
        
        if($pessoa->getId()) {
            $pessoa->setEdicao(new DateTime())
                    ->setEditor($this->security->getUser())
                    ->setEditorColaborador($this->session->get('colaborador', null));
        } else {
            $pessoa->setCriacao(new DateTime())
                    ->setCriador($this->security->getUser())
                    ->setCriadorColaborador($this->session->get('colaborador', null));
        }
        
        return $this->_save($pessoa);
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
            } else if ($atrib == 'tipo') {
                if ($valor != 0)
                    $query->andWhere($from . '.tipo = :tipo')->setParameter('tipo', (int)$valor);
            } else if ($atrib == 'nome=') {
                if ($valor != '')
                    $query->andWhere($from . '.nome = :nome_equal')->setParameter('nome_equal', $valor);
            } else if ($atrib == 'doc=') {
                if ($valor != '')
                    $query->andWhere($from . '.doc = :doc_equal')->setParameter('doc_equal', $valor);
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
            } else if ($atrib == 'comissionado') {
                if($valor instanceof Colaboradores)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.comissionado = :comissionado')->setParameter('comissionado', (int)$valor);
            } else if ($atrib == 'nome_apelido_razao_social') {
                if ($valor != '')
                    $query->andWhere($from . '.nome like :nome_apelido_razao_social or ' . $from . '.apelido like :nome_apelido_razao_social or ' . $from . '.razaoSocial like :nome_apelido_razao_social')->setParameter('nome_apelido_razao_social', TextService::spaceToPercent($valor));
            } else if ($atrib == 'nome_doc_apelido_razao_social') {
                if ($valor != '')
                    $query->andWhere($from . '.nome like :nome_doc_apelido_razao_social or ' . $from . '.doc like :nome_doc_apelido_razao_social or ' . $from . '.apelido like :nome_doc_apelido_razao_social or ' . $from . '.razaoSocial like :nome_doc_apelido_razao_social')->setParameter('nome_doc_apelido_razao_social', TextService::spaceToPercent($valor));
            } else if ($atrib == 'roleCliente') {
                if (!is_null($valor))
                    $query->andWhere($from . '.roleCliente = :roleCliente')->setParameter('roleCliente', $valor);
            } else if ($atrib == 'roleOftalmologista') {
                if (!is_null($valor))
                    $query->andWhere($from . '.roleOftalmologista = :roleOftalmologista')->setParameter('roleOftalmologista', $valor);
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
    
    public function saveImagem(Pessoas $pessoa, ?UploadedFile $imagem) {
        $this->checkEntityClass($pessoa, true);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        $imagemPath = '';
        if($imagem) {
            if(!$this->isAllowedFileType($imagem, 'imagens'))
                $errors[] = 'Tipo de arquivo não permitido (' . $imagem->getMimeType() . ')';
            $originalName = pathinfo($imagem->getClientOriginalName(), PATHINFO_FILENAME);
            $ext = $imagem->getClientOriginalExtension()?$imagem->getClientOriginalExtension():($imagem->guessExtension()?$imagem->guessExtension():'tmp');
            $fileName = FilesService::getFileDatePrefix() . 'pessoa_' . $pessoa->getId() . '_imagem.' . $ext;
            if(!$imagem->move(FilesService::getDatePath($this->getPublicFilesPath()), $fileName))
                die('Não foi possível mover o arquivo (' . $fileName . '[' . $originalName . '])');
            $imagemPath = FilesService::getDatePath($this->getPublicFilesPath(), true) . '/' . $fileName;
        } else
            $errors[] = 'Nenhuma imagem selecionada. Por favor, selecione imagem para a Pessoa.';
        
        if(count($errors)) {
            if($imagemPath)
                FilesService::removeFile($imagemPath, $this->getPublicFilesPath());
            return $errors;
        }
        
        if($imagemPath) {
            if($pessoa->getImagem())
                FilesService::removeFile($pessoa->getImagem(), $this->getPublicFilesPath());
            $pessoa->setImagem($imagemPath);
        }
        
        return $this->_save($pessoa);
    }

    public function deleteImagem(Pessoas $pessoa) {
        FilesService::removeFile($pessoa->getImagem(), $this->getPublicFilesPath());
        $pessoa->setImagem(null);
        return $this->_save($pessoa);
    }

    public function isAllowedFileType(?File $file, $escopo = null)
    {
        if(in_array($file->getMimeType(), Constants::arquivosMimeTypes($escopo)))
            return true;
        return false;
    }
    
    public function setPessoasVinculos(?Pessoas $pessoa, PessoasVinculosMapper $pessoasVinculosMapper)
    {
        $this->checkEntityClass($pessoa, false, true);
        $pessoa->setPessoasVinculos($pessoasVinculosMapper->select(['pessoa1_pessoa2' => $pessoa], ['ativo' => 'desc']));
        if($pessoa->view('tipo_cnpj?'))
            $pessoa->setPessoasVinculosContatos($pessoasVinculosMapper->select(['pessoa1_pessoa2' => $pessoa, 'pessoa1_pessoa2_vinculo' => 'contato', 'ativo' => 1], ['ativo' => 'desc']));
        
        return $pessoa;
    }
    
    /**************************************************************************************   / REGRA DE NEGÓCIO   ***/
    
}


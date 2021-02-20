<?php

namespace App\Mapper;

use App\Entity\Arquivos;
use App\Mapper\AbstractMapper;
use App\Services\Constants;
use App\Services\FilesService;
use App\Services\TextService;
use DateTime;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;

class ArquivosMapper extends AbstractMapper
{
    //'id', 'criacao', 'criador', 'criador_colaborador', 'edicao', 'editor', 'editor_colaborador', 'loja', 'pessoa', 'pedido', 'pagamento', 'cotacao', 'compra', 'despesa', 'nf', 'tipo', 'nome', 'arquivo', 'ext', 'tamanho', 'obs'
    protected $entityClass = Arquivos::class;
    protected $selectFrom = 'a';
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($arquivo, $data) { // Arquivos 
        $this->checkEntityClass($arquivo);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$arquivo->getLoja())
            $errors[] = 'Loja não identificada';
        // 'pedido', 'pagamento', 'cotacao', 'compra', 'despesa', 'nf',
        if(!$data->getNome())
            $errors[] = 'O campo Nome não pode estar vazio';
        if(!$data->getTipo())
            $errors[] = 'O campo Tipo não pode estar vazio';
        if(1==2) {
            // Arquivos duplicados???
            if(count($this->select(['doc='=>$data->getDoc(), 'loja'=>$data->getLoja(), 'id!='=>$arquivo->getId()])))
                $errors[] = 'Um Arquivo com o mesmo CPF/CNPJ já foi cadastrado. Por favor, informe outro CPF/CNPJ para este Arquivo';
        } else if(1 == 3) {
            if(count($this->select(['nome='=>$data->getNome(), 'loja'=>$data->getLoja(), 'id!='=>$arquivo->getId()])))
                $errors[] = 'Um Arquivo com o mesmo NOME já foi cadastrado. Por favor, informe outro Nome para este Arquivo';
        }
        if(!$arquivo->getId() && !$data->getArquivo())
            $errors[] = 'Por favor, selecione um arquivo!';
        
        if(count($errors)) {
            if($data->getArquivo())
                FilesService::removeFile($data->getArquivo(), $this->getProtectedFilesPath());
            return $errors;
        }
        
        if($arquivo->getArquivo() && $arquivo->getArquivo() != $data->getArquivo())
            FilesService::removeFile ($arquivo->getArquivo(), $this->getProtectedFilesPath());
        
        $arquivo->setTipo($data->getTipo())
                ->setNome($data->getNome())
                ->setArquivo($data->getArquivo())
                ->setExt($data->getExt())
                ->setTamanho($data->getTamanho())
                ->setObs($data->getObs())
                ;
        
        if($arquivo->getId()) {
            $arquivo->setEdicao(new DateTime())
                    ->setEditor($this->security->getUser())
                    ->setEditorColaborador($this->session->get('colaborador', null));
        } else {
            $arquivo->setCriacao(new DateTime())
                    ->setCriador($this->security->getUser())
                    ->setCriadorColaborador($this->session->get('colaborador', null));
        }
        
        return $this->_save($arquivo);
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
                if($valor instanceof \App\Entity\Pessoas)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.pessoa = :pessoa')->setParameter('pessoa', (int)$valor);
            } else if ($atrib == 'cidade') {
                if($valor instanceof Cidades)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.cidade = :cidade')->setParameter('cidade', (int)$valor);
            } else if ($atrib == 'nome=') {
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
    
    /****************************************************************************************   REGRA DE NEGÓCIO   ***/
    
    public function saveArquivo(Arquivos $arquivo, Arquivos $data, ?UploadedFile $uploadedFile) {
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if($uploadedFile) {
            if(!$this->isAllowedFileType($uploadedFile, $arquivo))
                $errors[] = 'Tipo de arquivo não permitido (' . $uploadedFile->getMimeType() . ')';
            $originalName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $data->setNome($data->getNome()?$data->getNome():$originalName)
                    ->setExt($uploadedFile->getClientOriginalExtension()?$uploadedFile->getClientOriginalExtension():($uploadedFile->guessExtension()?$uploadedFile->guessExtension():'tmp'))
                    ->setTamanho($uploadedFile->getSize());
            $fileName = FilesService::getFileDatePrefix() . 'loja_' . $arquivo->getLoja()->getId() . '_arquivo.' . $data->getExt();
            if(!$uploadedFile->move(FilesService::getDatePath($this->getProtectedFilesPath()), $fileName))
                die('Não foi possível mover o arquivo (' . $fileName . '[' . $originalName . '])');
            $data->setArquivo(FilesService::getDatePath($this->getProtectedFilesPath(), true) . '/' . $fileName);
        }
        
        if(count($errors)) {
            if($data->getArquivo())
                FilesService::removeFile($data->getArquivo(), $this->getProtectedFilesPath());
            return $errors;
        }
        
        return $this->save($arquivo, $data);
    }

    public function saveArquivos(Arquivos $arquivosEntity, array $data, $arquivos, $filePath) {
        $this->checkEntityClass($arquivosEntity);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        $nome = isset($data['nome'])?$data['nome']:null;
        $tipo = isset($data['tipo'])?$data['tipo']:'sem_tipo';
        $obs = isset($data['obs'])?$data['obs']:null;
        
        if(!is_array($arquivos) || !count($arquivos)) 
            $errors[] = 'Registro de arquivos não encontrado';
        
        foreach ($arquivos as $arquivo) {
            $file = new File($filePath . $arquivo->getArquivo());
            if(!$this->isAllowedFileType($file, $arquivosEntity))
                $errors[] = $arquivo->getNome() . ': Tipo de arquivo não permitido (' . $file->getMimeType() . ')';
            if(!$arquivo->getNome() && !$nome)
                $errors[] = 'Nome do arquivo não identificado';
            if(!$arquivo->getExt())
                $errors[] = $arquivo->getNome() . ': extensão de arquivo não identificado';
            if(!$arquivo->getTamanho())
                $errors[] = $arquivo->getNome() . ': tamanho de arquivo não definido';
        }
        
        if(count($errors)) {
            return $errors;
        }
        
        $i = 1;
        $n = '';
        $padLen = count($arquivos) < 100?2:strlen(count($arquivos));
        foreach ($arquivos as $arquivo) {
            $file = new File($filePath . $arquivo->getArquivo());
            $fileName = FilesService::getFileDatePrefix() . 'loja_' . $arquivosEntity->getLoja()->getId() . '_arquivo.' . $arquivo->getExt();
            if(!$file->move(FilesService::getDatePath($this->getProtectedFilesPath()), $fileName))
                die('Não foi possível mover o arquivo (' . $fileName . '[' . $arquivo->getNome() . '])');
            $arquivo->setArquivo(FilesService::getDatePath($this->getProtectedFilesPath(), true) . '/' . $fileName);
            
            $n = str_pad($i++, $padLen , '0', STR_PAD_LEFT);
            $arquivo_nome = $nome? $nome . ' ' . $n : $arquivo->getNome();
            $arquivo->setLoja($arquivosEntity->getLoja())
                    ->setPessoa($arquivosEntity->getPessoa())
                    ->setPedido($arquivosEntity->getPedido())
                    ->setPagamento($arquivosEntity->getPagamento())
                    ->setCotacao($arquivosEntity->getCotacao())
                    ->setCompra($arquivosEntity->getCompra())
                    ->setDespesa($arquivosEntity->getDespesa())
                    ->setNf($arquivosEntity->getNf())
                    ->setTipo($tipo)
                    ->setNome($arquivo_nome)
                    ->setObs($obs)
                    ;
            if(!$this->save($arquivo, $arquivo)) {
                return ['Erro ao tentar salvar arquivo (' . $arquivo->getNome() . '). Verifique se outros arquivos foram salvos com sucesso.'];
            }
        }
        
        return $arquivosEntity;
    }
    
    public function isAllowedFileType(?File $file, ?Arquivos $arquivo = null)
    {
        $escopo = null;
        if($arquivo)
            $escopo = $arquivo->view('escopo');
        if(in_array($file->getMimeType(), Constants::arquivosMimeTypes($escopo)))
            return true;
        return false;
    }

    /**************************************************************************************   / REGRA DE NEGÓCIO   ***/
    
}


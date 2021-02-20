<?php

namespace App\Mapper;

use App\Entity\PessoasReceitas;
use App\Mapper\AbstractMapper;
use App\Services\Constants;
use App\Services\DateService;
use App\Services\FilesService;
use App\Services\TextService;
use DateTime;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PessoasReceitasMapper extends AbstractMapper
{
    // 'id', 'criacao', 'criador', 'criador_colaborador', 'edicao', 'editor', 'editor_colaborador', 'pessoa', 'data_consulta', 'oftalmologista', 'longe_od_esf', 'longe_od_cil', 'longe_od_eixo', 'longe_oe_esf', 'longe_oe_cil', 'longe_oe_eixo', 'adicao', 'perto_od_esf', 'perto_od_cil', 'perto_od_eixo', 'perto_oe_esf', 'perto_oe_cil', 'perto_oe_eixo', 'arquivo', 'obs'
    protected $entityClass = PessoasReceitas::class;
    protected $selectFrom = 'pr';
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    protected function setUp()
    {
        /* Configurando o Abstract com informações deste Mapper... */
    }
    
    public function save($pessoaReceita, $data) { // PessoasReceitas 
        $this->checkEntityClass($pessoaReceita);
        $this->checkEntityClass($data);
        $errors = []; // VETOR DE ERROS: $errors[]='______';
        
        if(!$pessoaReceita->getPessoa() && !$data->getPessoa()) {
            $errors[] = 'O campo Pessoa não pode estar vazio';
        }
        
        if(count($this->select(['pessoa'=>$data->getPessoa()?$data->getPessoa():$pessoaReceita->getPessoa(), 'data_consulta'=>$data->getDataConsulta(), 'id!='=>$data->getId()])))
            $errors[] = 'Uma Consulta Oftalmológica com a mesma data já foi adicionada. Por favor, informe um outra Data de Consulta';
        
        if(count($errors)) {            
            return $errors;
        }
        
        if($data->getAdicao() < 0)
            $this->flash('Foi passado um valor negativo para a ADIÇÃO. Verifique se este valor está correto.', 'w');
        if($data->getLongeOdCil() > 0 || $data->getLongeOeCil() > 0 || $data->getPertoOdCil() > 0 || $data->getPertoOeCil() > 0)
            $this->flash('Foi passado um valor positivo para uma DIOPTRIA CILÍNDRICA. Verifique se os valores estão corretos.', 'w');
        
        $pessoaReceita->setPessoa($data->getPessoa()?$data->getPessoa():$pessoaReceita->getPessoa())
            ->setDataConsulta($data->getDataConsulta())
            ->setOftalmologista($data->getOftalmologista())
            ->setLongeOdEsf($data->getLongeOdEsf())
            ->setLongeOdCil($data->getLongeOdCil())
            ->setLongeOdEixo($data->getLongeOdEixo())
            ->setLongeOeEsf($data->getLongeOeEsf())
            ->setLongeOeCil($data->getLongeOeCil())
            ->setLongeOeEixo($data->getLongeOeEixo())
            ->setAdicao($data->getAdicao())
            ->setPertoOdEsf($data->getPertoOdEsf())
            ->setPertoOdCil($data->getPertoOdCil())
            ->setPertoOdEixo($data->getPertoOdEixo())
            ->setPertoOeEsf($data->getPertoOeEsf())
            ->setPertoOeCil($data->getPertoOeCil())
            ->setPertoOeEixo($data->getPertoOeEixo())
            ->setObs($data->getObs())
                ;
        
        if($pessoaReceita->getId()) {
            $pessoaReceita->setEdicao(new DateTime())
                    ->setEditor($this->security->getUser())
                    ->setEditorColaborador($this->session->get('colaborador', null));
        } else {
            $pessoaReceita->setCriacao(new DateTime())
                    ->setCriador($this->security->getUser())
                    ->setCriadorColaborador($this->session->get('colaborador', null));
        }
        
        return $this->_save($pessoaReceita);
    }

    public function select(array $atribs = array(), $orderBy = array(), $page = null, $join = array()) {
        $from = $this->selectFrom;
        $query = $this->repository->createQueryBuilder($from);
        /***********************************************************************************************   JOIN    ***/
        if(!is_array($join))
            $join = array($join);
        foreach ($join as $table) {
            if($table == 'pessoas') {
                $pessoas_from = 'p';
                $query->innerJoin($from . '.pessoa', $pessoas_from)->addSelect($pessoas_from);
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
            } else if ($atrib == 'pessoa') {
                if($valor instanceof \App\Entity\Pessoas)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.pessoa = :pessoa')->setParameter('pessoa', (int)$valor);
            } else if ($atrib == 'data_consulta') {
                if($valor instanceof DateTime)
                    $query->andWhere($from . '.dataConsulta = :dataConsulta')->setParameter('dataConsulta', $valor);
                else if ($valor != '')
                    $query->andWhere($from . '.dataConsulta = :dataConsulta')->setParameter('dataConsulta', DateService::converte($valor, 'en'));
            } else if ($atrib == 'data_consulta_de') {
                if($valor instanceof DateTime)
                    $query->andWhere($from . '.dataConsulta >= :dataConsultaDe')->setParameter('dataConsultaDe', $valor);
                else if ($valor != '')
                    $query->andWhere($from . '.dataConsulta >= :dataConsultaDe')->setParameter('dataConsultaDe', DateService::converte($valor, 'en'));
            } else if ($atrib == 'data_consulta_ate') {
                if($valor instanceof DateTime)
                    $query->andWhere($from . '.dataConsulta <= :dataConsultaAte')->setParameter('dataConsultaAte', $valor);
                else if ($valor != '')
                    $query->andWhere($from . '.dataConsulta <= :dataConsultaAte')->setParameter('dataConsultaAte', DateService::converte($valor, 'en'));
            } else if ($atrib == 'oftalmologista') {
                if($valor instanceof \App\Entity\Pessoas)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($from . '.oftalmologista = :oftalmologista')->setParameter('oftalmologista', (int)$valor);
            } else if ($atrib == 'ativo') {
                if (is_numeric($valor))
                    $query->andWhere($from . '.ativo = :ativo')->setParameter('ativo', (int)$valor);
            } else if ($atrib == 'limit') {
                if (is_numeric($valor))
                    $query->setMaxResults($valor);
            } else if ($atrib == 'pessoa_loja') {
                if($valor instanceof \App\Entity\Lojas)
                    $valor = $valor->getId();
                if ($valor != 0)
                    $query->andWhere($pessoas_from . '.loja = :pessoa_loja')->setParameter('pessoa_loja', (int)$valor);
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
    
    public function saveArquivoSession(PessoasReceitas $pessoaReceita, $uuid = null) {
        $key = $uuid?$uuid:($pessoaReceita->getId()?$pessoaReceita->getId():null);
        if(!$key)
            return false;
        
        $arquivos = $this->session()->get('pessoas-receitas-arquivos', []);
        
        if(array_key_exists($key, $arquivos)) {
            $arquivoData = $arquivos[$key];
            if($pessoaReceita->getArquivo()) {
                FilesService::removeFile($pessoaReceita->getArquivo(), $this->getProtectedFilesPath());
            }
            $file = new File($this->getSessionFilesPath() . $arquivoData['filename']);
            $fileName = FilesService::getFileDatePrefix() . 'loja_' . $pessoaReceita->getPessoa()->getLoja()->getId() . '_pessoa_' . $pessoaReceita->getPessoa()->getId() . '_receita.' . $file->getExtension();
            if(!$file->move(FilesService::getDatePath($this->getProtectedFilesPath()), $fileName))
                die('Não foi possível mover o arquivo (' . $fileName . ')');
            $pessoaReceita->setArquivo(FilesService::getDatePath($this->getProtectedFilesPath(), true) . '/' . $fileName);
            unset($arquivos[$key]);
            $this->session()->set('pessoas-receitas-arquivos', $arquivos);
            
            return $this->_save($pessoaReceita);
        }
    }

    public function calcGrauDePerto(PessoasReceitas $receita) : PessoasReceitas
    {
        if($receita->getAdicao() > 0) {
            // Perto Esférico
            if(!$receita->getPertoOdEsf())
                $receita->setPertoOdEsf($receita->getLongeOdEsf() + $receita->getAdicao());
            if(!$receita->getPertoOeEsf())
                $receita->setPertoOeEsf($receita->getLongeOeEsf() + $receita->getAdicao()); // fórmula
            // Perto Cilindrico e Eixo
            if(!$receita->getPertoOdCil())
                $receita->setPertoOdCil($receita->getLongeOdCil());
            if(!$receita->getPertoOdEixo())
                $receita->setPertoOdEixo($receita->getLongeOdEixo());
            if(!$receita->getPertoOeCil())
                $receita->setPertoOeCil($receita->getLongeOeCil());
            if(!$receita->getPertoOeEixo())
                $receita->setPertoOeEixo($receita->getLongeOeEixo());
        }
        if(($receita->getPertoOdEsf() || $receita->getPertoOeEsf()) && !$receita->getAdicao()) {
            $adOd = $receita->getPertoOdEsf() - $receita->getLongeOdEsf();
            $adOe = $receita->getPertoOeEsf() - $receita->getLongeOeEsf();
            $receita->setAdicao($adOd);
            if($adOd != $adOe && !$adOd)
                $receita->setAdicao($adOe);
        }
        
        return $receita;
    }
    
    public function deleteArquivo(PessoasReceitas $pessoaReceita) {
        if(!$pessoaReceita->getId())
            return false;
        
        if($pessoaReceita->getArquivo()) {
            FilesService::removeFile($pessoaReceita->getArquivo(), $this->getProtectedFilesPath());
            $pessoaReceita->setArquivo(null);
        }
        return $this->_save($pessoaReceita);
    }

    /**************************************************************************************   / REGRA DE NEGÓCIO   ***/
    
}


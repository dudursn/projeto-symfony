<?php

namespace App\Services;

use App\Entity\Arquivos;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SessionFilesService
{
    private $sessionPath;
    private $sessionName = 'app_uploaded_files';
    private $session;
    private $fileSystem;
    private $parameterBag;
    
    public function __construct(SessionInterface $session, RequestStack $requestStack, Filesystem $fileSystem, ParameterBagInterface $parameterBag) {
        $this->session = $session;
        $this->fileSystem = $fileSystem;
        $this->requestStack = $requestStack;
        $this->request = $requestStack->getCurrentRequest();
        $this->parameterBag = $parameterBag;
        $this->sessionPath = $this->getSessionFilesPath();
    }
    
    private function sessionAdd($uuid, Arquivos $arquivo)
    {
        $arquivos = $this->session->get($this->sessionName, []);
        
        if(array_key_exists($uuid, $arquivos)) {
            return ['Arquivo já salvo anteriormente (UUID já configurada)'];
        }
        $arquivos[$uuid] = $arquivo;
        $this->session->set($this->sessionName, $arquivos);
        return $this;
    }
    
    private function sessionGet($uuid = null) {
        $arquivos = $this->session->get($this->sessionName, []);
        if(is_null($uuid)) {
            return $arquivos;
        } else {
            if(array_key_exists($uuid, $arquivos))
                return $arquivos[$uuid];
            else
                return null;
        }
    }
    
    private function sessionRemove($uuid = null) {
        if(!$this->sessionPath)
            die('SessionFilesService::sessionPath não definida');
        $arquivos = $this->session->get($this->sessionName, []);
        if(is_null($uuid)) {
            foreach ($arquivos as $uuid => $arquivo)
                $this->fileSystem->remove($this->sessionPath . $arquivo->getArquivo());
            $this->session->remove($this->sessionName);
            return $this;
        } 
        if(!array_key_exists($uuid, $arquivos)) {
            return ['Arquivo não localizado na Sessão (UUID Key não encontrada)'];
        }
        $this->fileSystem->remove($this->sessionPath . $arquivos[$uuid]->getArquivo());
        unset($arquivos[$uuid]);
        
        $this->session->set($this->sessionName, $arquivos);
        return $this;
    }
    
    public function add($uuid, UploadedFile $uploadedFile, $nome = null)
    {
        if(!$this->sessionPath)
            die('SessionFilesService::sessionPath não definida');
        $arquivo = new Arquivos();
        $arquivo->setNome($nome?$nome:pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME))
                ->setExt($uploadedFile->getClientOriginalExtension()?$uploadedFile->getClientOriginalExtension():($uploadedFile->guessExtension()?$uploadedFile->guessExtension():'tmp'))
                ->setTamanho($uploadedFile->getSize())
                ;
        $fileName = FilesService::getFileDatePrefix() . 'arquivo.' . $arquivo->getExt();
        if(!$uploadedFile->move(FilesService::getDatePath($this->sessionPath), $fileName))
            die('Não foi possível mover o arquivo (' . $fileName . ' [' . $arquivo->getNome() . '])');
        $arquivo->setArquivo(FilesService::getDatePath($this->sessionPath, true) . '/' . $fileName);
        return $this->sessionAdd($uuid, $arquivo);
    }
    
    public function get($uuid) {
        if(!$uuid)
            return null;
        return $this->sessionGet($uuid);
    }
    
    public function getAll() {
        return $this->sessionGet();
    }
    
    public function remove($uuid) {
        return $this->sessionRemove($uuid);
    }
    
    public function removeAll() {
        return $this->sessionRemove();
    }
    
    public function getProtectedFilesPath(): ?string
    {
        return $this->parameterBag->get('protected_files');
    }
    
    public function getPublicFilesPath(): ?string
    {
        return $this->parameterBag->get('public_files');
    }
    
    public function getSessionFilesPath(): ?string
    {
        return $this->parameterBag->get('session_files');
    }
    
}


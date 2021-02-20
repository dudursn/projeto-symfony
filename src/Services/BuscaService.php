<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class BuscaService {
    
    private $session;
    private $request;
    private $requestStack;
    private $session_name = 'app_busca';
    private $busca_name;
    private $pageParameterName;
    private $page;
    private $isPost;
    private $isReset;
    private $queryData; //Not session data, but returned on get
    
    public function __construct(SessionInterface $session, RequestStack $requestStack) {
        $this->session = $session;
        $this->requestStack = $requestStack;
        $this->request = $requestStack->getCurrentRequest();
        $this->queryData = [];
    }
    
    private function sessionSet($data = null, $value = null, $reset = false) {
        $app_busca_array = $this->session->get($this->session_name, []);
        
        if($reset === true)
            unset($app_busca_array[$this->busca_name]);
        if(!array_key_exists($this->busca_name, $app_busca_array)) {
            $app_busca_array[$this->busca_name] = [];
        } 
        
        if(is_array($data)) { /***   replaceArray   ***/
            $app_busca_array[$this->busca_name] = array_merge($app_busca_array[$this->busca_name], $data);
        } else if($data) { /***   setar Key especifica   ***/
            $app_busca_array[$this->busca_name][$data] = $value;
        }
        
        $this->session->set($this->session_name, $app_busca_array);
        return $this;
    }
    
    private function sessionGet($key = null) {
        if(!$this->busca_name)
            die ('Método BuscaService::setUp() não foi executado');
        
        $app_busca_array = $this->session->get($this->session_name, []);
        if(is_null($key)) {
            return $app_busca_array[$this->busca_name];
        } else {
            if(array_key_exists($key, $app_busca_array[$this->busca_name]))
                return $app_busca_array[$this->busca_name][$key];
            else
                return null;
        }
    }
    
    private function sessionRemove($key = null) {
        if(!$this->busca_name)
            die ('Método BuscaService::setUp() não foi executado');
        
        if(is_null($key)) {
            $this->sessionSet(null, null, true);
            return $this;
        } 
        
        $app_busca_array = $this->session->get($this->session_name);
        if(array_key_exists($key, $app_busca_array[$this->busca_name]))
            unset($app_busca_array[$this->busca_name][$key]);
        
        $this->session->set($this->session_name, $app_busca_array);
        return $this;
    }
    
    private function sessionDestroy() {
        $this->session->remove($this->session_name);
    }
    
    public function setUp($form, $busca_name = null, $pageParameterName = 'page') {
        $this->busca_name = $busca_name ?? $this->request->get('_route'); // STRING: usar o texto passado; NULL: para usar a "_route" atual (nome da rota atual)
        $this->pageParameterName = $pageParameterName;
        $this->isPost = $this->isReset = false;
        $this->sessionSet();
        
        $cloneForm = clone $form;
        $cloneForm->handleRequest($this->request);
        
        if ($cloneForm->isSubmitted()) {
            $this->removeAll();
            $this->isPost = true;
            if(array_key_exists('pesquisa_reset', $this->request->request->all())) {
                $this->isReset = true;
            } else {
                $this->sessionSet($cloneForm->getData());
                $form->setData($cloneForm->getData());
            }
            $this->setPage(1);
        } else {
            $form->setData($this->get());
            $this->setPage($this->request->query->getInt($this->pageParameterName, 1));
        }
        
        return $this;
    }
    
    public function get($filtro = null, $default = null) {
        if(is_null($filtro)) {
            return array_merge($this->sessionGet(), $this->queryData);
        } else {
            $array = array_merge($this->sessionGet(), $this->queryData);
            return array_key_exists($filtro, $array)?$array[$filtro]:$default;
        }
    }
    
    public function set($filtro = null, $value = null) {
        if(is_array($filtro))
            $this->sessionSet($filtro);
        else
            $this->sessionSet($filtro, $value);
        return $this;
    }
    
    public function getSession($filtro = null, $default = null) {
        if(is_null($filtro)) {
            return $this->sessionGet();
        } else {
            return $this->sessionGet($filtro) ?? $default;
        }
    }
    
    public function remove($filtro) {
        $this->sessionRemove($filtro);
        return $this;
    }
    
    public function removeAll() {
        $this->sessionRemove();
        return $this;
    }
    
    public function getPage($default = 1) {
        return $this->page ?? $default;
    }
    
    public function setPage($page) {
        $this->page = (int)$page;
        return $this;
    }
    
    public function getPageParameterName($default = 'page') {
        return $this->pageParameterName ?? $default;
    }
    
    public function isPost() {
        return $this->isPost === true?true:false;
    }
    
    public function isReset() {
        return $this->isReset === true?true:false;
    }
    
    public function getRequest() {
        return $this->request;
    }
    
    public function destroy() {
        $this->sessionDestroy();
    }
    
    // .....................................................   P R A   B A I X O   .....................................
    
    public function setQuery($filtro = null, $value = null) {
        if(is_array($filtro))
            $this->querySet($filtro);
        else
            $this->querySet($filtro, $value);
        return $this;
    }
    
    public function getQuery($filtro = null, $default = null) {
        if(is_null($filtro)) {
            return $this->queryGet();
        } else {
            return $this->queryGet($filtro) ?? $default;
        }
    }
    
    public function removeQuery($filtro) {
        $this->queryRemove($filtro);
        return $this;
    }
    
    public function removeQueryAll() {
        $this->queryRemove();
        return $this;
    }
    
    private function querySet($data = null, $value = null, $reset = false) {
        if(is_array($data)) { /***   replaceArray   ***/
            $this->queryData = array_merge($this->queryData, $data);
        } else if($data) { /***   setar Key especifica   ***/
            $this->queryData[$data] = $value;
        }
        return $this;
    }
    
    private function queryGet($key = null) {
        if(is_null($key)) {
            return $this->queryData;
        } else {
            if(array_key_exists($key, $this->queryData))
                return $this->queryData[$key];
            else
                return null;
        }
    }
    
    private function queryRemove($key = null) {
        if(is_null($key)) {
            $this->queryData = [];
            return $this;
        } 
        
        if(array_key_exists($key, $this->queryData))
            unset($this->queryData[$key]);
        return $this;
    }
    
    /*
     * setUp(form, busca_name = null, pageParameterName = null)
     *    Incia/Configura a busca. Recebe os valores do form de pesquisa e organiza o array na Sessão
     *    FORM:       O form contendo os dados os valores a serem salvos na sessão
     *    BUSCA_NAME: O nome da busca atual. PADRÃO: nome da rota atual
     *    PAGEPARAMETERNAME: O nome dado ao parametro página da Request Query que será usado pelo PAGINATOR (para o caso de dois PAGINATORS por página)
     *       Valor padrão: o valor padrão do PAGINATOR: "page"
     * get(filtro = null, padrao = null): resgata os valores na sessão
     *    Filtro é Null: retorna um array com todos os filtros da busca atual
     *    Filtro é String: retorna o valor do filtro especificado
     *    Padrao: Se a key "filtro" não for encontrado, ou se o array "busca_name" não for encotrado, retorna o valor em PADRAO
     * set(filtro, valor = null)
     *    insere valores na sessão
     *    FILTRO é string: adiciona o VALOR referente apenas ao key filtro
     *    FILTRO é array: modelo "key => valor", adiciona os keys com seus valores ao array existente no BUSCA_NAME
     *       O array em FILTRO substitui os KEYS previamente existente no array de BUSCA_NAME
     *    VALOR: se FILTRO não for array, o valor a ser adicionado na key FILTRO. Se FILTRO for array, o valor em VALOR é desconsiderado
     * getPage(padrao = 1): Pega a página atual resgistrado na busca
     *    PADRAO: Caso nenhuma página tenha sido configurada, retorna o valor em PADRÃO
     * set(page)
     *    Configura a página atual na busca. VALOR OBTIDO AUTOMATICAMENTE DURANTE O SetUP DA BUSCA
     *    PAGE: O valor a ser configurado para a página na busca
     * isPost():
     *    Retorna se o Request atual é POST (para saber se o botão PESQUISAR for clicado
     * isReset():
     *    Retorna se o usuário soliciou o Reset da busca. Que a busca volte para seus valores iniciais. Botal RESET foi clicado
     */
    

    
}


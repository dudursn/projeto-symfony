<?php

namespace App\Mapper;

use App\Services\FlashMessages;
use App\Services\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

abstract class AbstractMapper
{
    protected $entityClass;
    protected $selectFrom;
    protected $em;
    protected $repository;
    protected $flashMessages;
    protected $paginator;
    protected $paginator_limit = 25;
    protected $request;
    protected $security;
    protected $passwordEncoder;
    protected $mail;
    protected $session;
    protected $parameterBag;
    
    public function __construct(EntityManagerInterface $em, FlashMessages $flash, PaginatorInterface $paginator, RequestStack $requestStack, Security $security, UserPasswordEncoderInterface $passwordEncoder, SessionInterface $session, MailService $mail, ParameterBagInterface $parameterBag)
    {
        $this->em = $em;
        $this->repository = $em->getRepository($this->entityClass);
        $this->flashMessages = $flash;
        $this->paginator = $paginator;
        $this->request = $requestStack->getCurrentRequest();
        $this->security = $security;
        $this->passwordEncoder = $passwordEncoder;
        $this->session = $session;
        $this->mail = $mail;
        $this->parameterBag = $parameterBag;
        $this->setUp();
    }
    
    /***************************************************************************************   MÉTODOS ABSTRATOS   ***/
    
    abstract protected function setUp();
    abstract protected function save($entity, $data);
    abstract protected function select(array $atribs = array(), $orderBy = array(), $page = null, $join = array());
    abstract protected function setDependencies($dependencies = array(), $entitys = array());
    
    /*************************************************************************************   / MÉTODOS ABSTRATOS   ***/
    
    /******************************************************************************************   MÉTODOS GERAIS   ***/
    
    public function checkEntityClass($entity, $id = false, $die = true)
    {
        if(!($entity instanceof $this->entityClass)) {
            if($die)
                die('AbstractMapper::checkEntityClass($entity): ' . $this->entityClass . ' expected.');
            return false;
        }
        if($id && !$entity->getId()) {
            if($die)
                die('AbstractMapper::checkEntityClass($entity): ID is EMPTY');
            return false;
        }
        return true;
    }
    
    public function _save($entity)
    {
        $this->checkEntityClass($entity);
        if(!$entity->getId()) {
            $this->em->persist($entity);
        }
        $this->em->flush();
        return $entity;
    }
    
    protected function _select($query, $orderBy = array(), $page = null)
    {
        /********************************************************************************************   ORDER BY   ***/
        if(is_null($orderBy))
            $orderBy = array();
        if(!is_array($orderBy))
            $orderBy = array($orderBy);
        foreach ($orderBy as $key => $value) {
            if(is_numeric($key)) {
                $sort = $value;
                $order = 'ASC';
            } else {
                $sort = $key;
                $order = $value;
            }
            $sort = strpos($sort, '.') === false?($this->selectFrom . '.' . $sort):$sort;
            $query->addOrderBy($sort, $order);
        }
        /******************************************************************************************   / ORDER BY   ***/
        /*******************************************************************************************   PRINT SQL   ***/
        //echo '<hr/>';echo $query->getQuery()->getSQL();echo '<hr/>';
        /*****************************************************************************************   / PRINT SQL   ***/
        if(is_null($page)) {
            return $query->getQuery()->execute(); //*************************************   RESULT SEM PAGINATOR   ***/
        } else if(is_string($page) && str_replace(array('-', '_'), '', strtolower($page)) === 'querybuilder') {
            return $query; //************************************************************   RETURN QUERY BUILDER   ***/
        } else {
            /**************************************************************************************   PAGINATION   ***/
            $paginator_data = array('page' => 1, 'limit' => $this->paginator_limit, 'options' => array());
            if(is_array($page)) {
                $paginator_data['page'] = array_key_exists(0, $page) && $page[0]?(int)$page[0]:1;
                $paginator_data['limit'] = array_key_exists(1, $page) && $page[1]?(int)$page[1]:$this->paginator_limit;
                $paginator_data['options'] = array_key_exists(2, $page) && count($page[2])?$page[2]:array();
            } else {
                $paginator_data['page'] = (int)$page;
            }
            return $this->paginator->paginate($query->getQuery(), $paginator_data['page'], $paginator_data['limit'], $paginator_data['options']);
            /************************************************************************************   / PAGINATION   ***/
        } 
    }
    
    public function find(?int $id)
    {
        return is_numeric($id) && $id > 0?$this->repository->find($id):null;
    }

    public function delete($entity)
    {
        $this->checkEntityClass($entity);
        $this->em->remove($entity);
        $this->em->flush();
        return true;
    }
    
    // paga array de Entitys pra excluir
    public function deleteGroup($group = array())
    {
        foreach ($group as $entity) {
            $this->checkEntityClass($entity);
            $this->em->remove($entity);
        }
        if(count($group))
            $this->em->flush();
        return true;
    }
    
    // paga um array de id's pra excluir. Ex: array(2, 6, 10, 23, 56) - vai pegar os IDs no array abrir as entidades e excluí-las
    public function deleteGroupArray($ids = array())
    {
        $entityGroup = [];
        foreach ($ids as $id) {
            if($entity = $this->find($id))
                $entityGroup[] = $entity;
        }
        $this->deleteGroup($entityGroup);
        return true;
    }
    
    public function flash($messages, $context = 's')
    {
        $this->flashMessages->add($messages, $context);
        return $this;
    }
    
    public function request(): Request
    {
        return $this->request;
    }
    
    public function session(): SessionInterface
    {
        return $this->session;
    }
    
    public function security(): Security
    {
        return $this->security;
    }
    
    public function getBaseUrl($sufixo = ''): string
    {
        return $this->request->getScheme() . '://' . $this->request->getHttpHost() . $this->request->getBasePath() . $sufixo;
    }
    
    public function getParam($param): ?string
    {
        return $this->parameterBag->get($param);
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
    
    /****************************************************************************************   / MÉTODOS GERAIS   ***/
    
}


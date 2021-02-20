<?php

namespace App\Form\EventListener;

use App\Mapper\LoginsMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;

class LoginsEventListener implements EventSubscriberInterface
{
    /**
     * O nome do Key no POST para resgatar o id da Cidade no Resquest->Post. PADRÃO: $event->getForm()->getParent()->getName()
     * 1. Não setar postKey (postKey === null): Usa o nome do formulário pai - $this->request->request->get(PARENT_NAME) [MODO PADRÃO]
     * 2. Setar postKey com uma STRING: O método vai pegar o valor da cidade em $this->request->request->get(STRING)[form_name]
     * 3. Setar postKey com "": O método vai pegar o valor da cidade em $this->request->request->get(form_name)
     **/
    private $postKey;
    private $loginsMapper;
    private $request;
    
    public function __construct(LoginsMapper $loginsMapper, RequestStack $requestStack) {
        $this->loginsMapper = $loginsMapper;
        $this->request= $requestStack->getCurrentRequest();
    }
    
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SET_DATA => 'onPostSetData',
        ];
    }

    public function onPostSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
        $form_name = $event->getForm()->getName();
        $parent = $event->getForm()->getParent();
        $parent_name = $event->getForm()->getParent()->getName();
        $choices = [];
        
        if($this->request->isMethod('POST') && !$this->request->request->get('pesquisa_reset')) {
            if(is_null($this->postKey)) {
                $post = $this->request->request->get($parent_name);
                $loginId = is_array($post) && isset($post[$form_name]) && $post[$form_name]?$post[$form_name]:null;
            } else if($this->postKey == '') {
                $loginId = $this->request->request->get($form_name, null);
            } else {
                $post = $this->request->request->get($this->postKey);
                $loginId = is_array($post) && isset($post[$form_name]) && $post[$form_name]?$post[$form_name]:null;
            }
            
            $postLogin = $this->loginsMapper->find($loginId);
            if($postLogin)
                $choices[] = $postLogin;
        }
        
        if(($data instanceof \App\Entity\Logins) && $data->getId())
            $choices[] = $data;
        
        if(count($choices)) {
            $options = $form->getConfig()->getOptions();
            $options['choices'] = $choices;
            $options['placeholder'] = false;
            $parent->add($form_name, EntityType::class, $options);
        }
    }

    public function onPreSubmit(FormEvent $event)
    {
        /*
        return;
        $data = $event->getData();
        $form = $event->getForm();
        $form_name = $event->getForm()->getName();
        $parent = $event->getForm()->getParent();
        //$parent_name = $event->getForm()->getParent()->getName();
        
        if($data && is_numeric($data)) {
            $options = $form->getConfig()->getOptions();
            if($cidade = $this->cidadesMapper->find($data)) {
                $options['choices'] = [$cidade];
                $options['placeholder'] = false;
            }
            $parent->add($form_name, EntityType::class, $options);
            $parent->get($form_name)->setData($cidade);
        }*/
    }
    
    public function onPostSubmit(FormEvent $event)
    {
        /*
        return;
        $data = $event->getData();
        //$form = $event->getForm();
        //$form_name = $event->getForm()->getName();
        $parent = $event->getForm()->getParent();
        //$parent_name = $event->getForm()->getParent()->getName();
        
        if($data && is_numeric($data)) {
            if($cidade = $this->cidadesMapper->find($data) && $parent->getData()) {
                $parent->getData()->setCidade($cidade);
            }
        }*/
    }
    
    public function setPostKey($postKey): CidadesEventListener
    {
        $this->postKey = $postKey;
        return $this;
    }
    
    public function getPostKey(): ?string
    {
        return $this->postKey;
    }
    
}
<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\RequestStack;

class FlashMessages {
    
    protected $request;
    protected $requestStack;
    
    public function __construct(RequestStack $requestStack) {
        $this->requestStack = $requestStack;
        $this->request = $requestStack->getCurrentRequest();
    }
    
    public function add($messages, $context = 's') {
        if(!$messages) return $this;
        if(!is_array($messages)) {
            $messages = array($messages);
        }
        foreach ($messages as $message)
            $this->request->getSession()->getFlashBag()->add($context, $message);
        
        return $this;
    }
    
}


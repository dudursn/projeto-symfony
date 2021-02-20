<?php

namespace App\Form\DataTransformer;

use App\Services\TextService;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class IntToGrauTransformer implements DataTransformerInterface
{
    private $eixo = false;
    
    public function transform($valor)
    {
        if($this->eixo)
            return $valor? ($valor . 'º') :'';
        return $valor? TextService::intToReal($valor, true, false):'';
    }

    public function reverseTransform($valor)
    {
        return TextService::realToInt($valor);
    }
    
    public function setEixo(bool $eixo): self
    {
        $this->eixo = $eixo;
        
        return $this;
    }
    
    public function getEixo(): bool
    {
        return $this->eixo;
    }
}
<?php

namespace App\Form\DataTransformer;

use App\Services\TextService;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class IntToRealTransformer implements DataTransformerInterface
{
    public function transform($valor)
    {
        return $valor? TextService::intToReal($valor):'';
    }

    public function reverseTransform($valor)
    {
        return TextService::realToInt($valor);
    }
}
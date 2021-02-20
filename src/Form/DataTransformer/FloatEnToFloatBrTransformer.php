<?php

namespace App\Form\DataTransformer;

use App\Services\TextService;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class FloatEnToFloatBrTransformer implements DataTransformerInterface
{
    public function transform($valor)
    {
        return (float)$valor? TextService::floatToText((float)$valor):'';
    }

    public function reverseTransform($valor)
    {
        return TextService::textToFloat($valor);
    }
}
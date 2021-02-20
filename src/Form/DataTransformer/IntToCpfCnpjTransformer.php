<?php

namespace App\Form\DataTransformer;

use App\Services\TextService;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class IntToCpfCnpjTransformer implements DataTransformerInterface
{
    public function transform($doc)
    {
        if(strlen($doc) === 11) {
            return TextService::maskAsCpf($doc);
        } else if(strlen($doc) === 14) {
            return TextService::maskAsCnpj($doc);
        }
        return null;
    }

    public function reverseTransform($doc)
    {
        if (!$doc)
            return null;
        return preg_replace('/[^0-9]/', '', $doc);
    }
}
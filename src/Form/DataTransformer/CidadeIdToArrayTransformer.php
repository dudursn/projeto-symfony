<?php

namespace App\Form\DataTransformer;

use App\Mapper\CidadesMapper;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CidadeIdToArrayTransformer implements DataTransformerInterface
{
    private $cidadesMapper;

    public function __construct(CidadesMapper $cidadesMapper)
    {
        $this->cidadesMapper = $cidadesMapper;
    }

    /**
     * Transforma uma Entidade Cidades em uma String, com sua ID
     *
     * @param  Issue|null $issue
     * @return string
     */
    public function transform($cidade)
    {
        echo '<hr>Id String -> Array Select: transform';
        echo $cidade instanceof \App\Entity\Cidades?'<hr>Cidade ID: ' . $cidade->getId():($cidade?('<hr> Não é Entity: ' . $cidade):'<hr>Cidade é VAZIO');
        
        //$this->cidadesMapper->flash('Chamou os transform!'); // Excluir depois
        /*
        if (null === $cidade)
            return null;
        */
        
        return 1;
        if(!$cidade)
            return null;
        //$this->cidadesMapper->flash('Retorna o Array: ' . $cidade->getNome()); // Excluir depois
        
        return ['São Luis' => 1];
        
        //return $cidade->getId();
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  string $issueNumber
     * @return Issue|null
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($cidadeId)
    {
        echo '<hr>Array Select -> Id String: reverseTransform';
        echo $cidadeId instanceof \App\Entity\Cidades?'<hr>Cidade ID: ' . $cidadeId->getId():($cidadeId?('<hr> Não é Entity: ' . $cidadeId):'<hr>Cidade é VAZIO');
        
        //$this->cidadesMapper->flash('Chamou os reversTransform: ' . $cidadeId); // Excluir depois
        // no issue number? It's optional, so that's ok
        if (!$cidadeId) {
            return;
        }
        
        $cidade = $this->cidadesMapper->find($cidadeId);
        $this->cidadesMapper->flash('Retorna a Entidade: ' . $cidade->getNome()); // Excluir depois
        
        if (null === $cidade) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'Não existe uma Cidade com o ID passado (%s)!',
                $cidadeId
            ));
        }

        return $cidade;
    }
}
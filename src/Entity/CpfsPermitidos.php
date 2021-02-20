<?php

namespace App\Entity;

use App\Services\TextService;
use App\Validator\Constraints as CustomAssert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CpfsPermitidosRepository")
 */
class CpfsPermitidos
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @CustomAssert\CpfCnpj
     */
    private $cpf;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCpf(): ?string
    {
        return $this->cpf;
    }

    public function setCpf(string $cpf): self
    {
        $this->cpf = $cpf;

        return $this;
    }
    
    public function getCpfFormatado() {
        return $this->getCpf()?TextService::maskAsCpf($this->getCpf()):'';
    }
}

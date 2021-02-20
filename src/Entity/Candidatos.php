<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CandidatosRepository")
 */
class Candidatos
{
    // id, criacao, criador, edicao, editor, eleicao, usuario, apelido, mandato, numero, info, votosQtd
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $criacao;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Logins")
     * @ORM\JoinColumn(nullable=false)
     */
    private $criador;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $edicao;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Logins")
     */
    private $editor;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Eleicoes", inversedBy="candidatos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $eleicao;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Usuarios", inversedBy="candidatos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $usuario;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $apelido;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mandato;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $numero;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $info;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $votosQtd;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Votos", mappedBy="candidato")
     */
    private $votos;

    public function __construct()
    {
        $this->votos= new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCriacao(): ?\DateTimeInterface
    {
        return $this->criacao;
    }

    public function setCriacao(\DateTimeInterface $criacao): self
    {
        $this->criacao = $criacao;

        return $this;
    }

    public function getCriador(): ?Logins
    {
        return $this->criador;
    }

    public function setCriador(?Logins $criador): self
    {
        $this->criador = $criador;

        return $this;
    }

    public function getEdicao(): ?\DateTimeInterface
    {
        return $this->edicao;
    }

    public function setEdicao(?\DateTimeInterface $edicao): self
    {
        $this->edicao = $edicao;

        return $this;
    }

    public function getEditor(): ?Logins
    {
        return $this->editor;
    }

    public function setEditor(?Logins $editor): self
    {
        $this->editor = $editor;

        return $this;
    }

    public function getEleicao(): ?Eleicoes
    {
        return $this->eleicao;
    }

    public function setEleicao(?Eleicoes $eleicao): self
    {
        $this->eleicao = $eleicao;

        return $this;
    }

    public function getUsuario(): ?Usuarios
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuarios $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getApelido(): ?string
    {
        return $this->apelido;
    }

    public function setApelido(string $apelido): self
    {
        $this->apelido = $apelido;

        return $this;
    }

    public function getMandato(): ?string
    {
        return $this->mandato;
    }

    public function setMandato(?string $mandato): self
    {
        $this->mandato = $mandato;

        return $this;
    }
    
    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getInfo(): ?string
    {
        return $this->info;
    }

    public function setInfo(?string $info): self
    {
        $this->info = $info;

        return $this;
    }

    public function getVotosQtd(): ?int
    {
        return $this->votosQtd;
    }

    public function setVotosQtd(?int $votosQtd): self
    {
        $this->votosQtd = $votosQtd;

        return $this;
    }

    /**
     * @return Collection|Votos[]
     */
    public function getVotos(): Collection
    {
        return $this->votos;
    }

    public function addVotos(Votos $votos): self
    {
        if (!$this->votos->contains($votos)) {
            $this->votos[] = $votos;
            $votos->setCandidato($this);
        }

        return $this;
    }

    public function removeVotos(Votos $votos): self
    {
        if ($this->votos->contains($votos)) {
            $this->votos->removeElement($votos);
            // set the owning side to null (unless already changed)
            if ($votos->getCandidato() === $this) {
                $votos->setCandidato(null);
            }
        }

        return $this;
    }
    
    public function view($atrib, $data = null) {
        if ($atrib == "criacao") {
            return $this->getCriacao()?$this->getCriacao()->format('d/m/Y H:i:s'):'';
        } else if ($atrib == "edicao") {
            return $this->getEdicao()?$this->getEdicao()->format('d/m/Y H:i:s'):'';
        } else if ($atrib == "criador_criador_colaborador") {
            return $this->getCriadorColaborador()?$this->getCriadorColaborador()->view('identificacao_nome'):$this->getCriador()->getNome();
        } else if ($atrib == "editor_editor_colaborador") {
            return $this->getEditorColaborador()?$this->getEditorColaborador()->view('identificacao_nome'):$this->getEditor()->getNome();
        } else if ($atrib == "votacao_inicio") {
            return $this->getVotacaoInicio()?$this->getVotacaoInicio()->format('d/m/Y'):'';
        } else if ($atrib == "votacao_fim") {
            return $this->getVotacaoFim()?$this->getVotacaoFim()->format('d/m/Y'):'';
        } else if ($atrib == "apuracao_data") {
            return $this->getApuracaoData()?$this->getApuracaoData()->format('d/m/Y'):'';
        } else if ($atrib == "mandato_nome") {
            return $this->getMandato()? \App\Services\Constants::candidatosMandatos($this->getMandato()):'Não Informado';
        } else if ($atrib == "info_br") {
            return nl2br($this->getInfo());
        } else {
            //para atributos que não precisam de algoritimo para mascarar para a view
            //return $this->get();
            return $atrib . ' (View Error)';
        }
    }    
}

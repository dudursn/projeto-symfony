<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EleicoesRepository")
 */
class Eleicoes
{
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
     * @ORM\Column(type="string", length=255)
     */
    private $ano;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $descricao;

    /**
     * @ORM\Column(type="date")
     */
    private $votacaoInicio;

    /**
     * @ORM\Column(type="date")
     */
    private $votacaoFim;

    /**
     * @ORM\Column(type="date")
     */
    private $apuracaoData;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $votosQtd;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Candidatos", mappedBy="eleicao")
     */
    private $candidatos;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Votos", mappedBy="eleicao")
     */
    private $votos;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $ativo;

    public function __construct()
    {
        $this->candidatos = new ArrayCollection();
        $this->votos = new ArrayCollection();
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

    public function getAno(): ?string
    {
        return $this->ano;
    }

    public function setAno(string $ano): self
    {
        $this->ano = $ano;

        return $this;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(?string $descricao): self
    {
        $this->descricao = $descricao;

        return $this;
    }

    public function getVotacaoInicio(): ?\DateTimeInterface
    {
        return $this->votacaoInicio;
    }

    public function setVotacaoInicio(\DateTimeInterface $votacaoInicio): self
    {
        $this->votacaoInicio = $votacaoInicio;

        return $this;
    }

    public function getVotacaoFim(): ?\DateTimeInterface
    {
        return $this->votacaoFim;
    }

    public function setVotacaoFim(\DateTimeInterface $votacaoFim): self
    {
        $this->votacaoFim = $votacaoFim;

        return $this;
    }

    public function getApuracaoData(): ?\DateTimeInterface
    {
        return $this->apuracaoData;
    }

    public function setApuracaoData(\DateTimeInterface $apuracaoData): self
    {
        $this->apuracaoData = $apuracaoData;

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
     * @return Collection|Candidatos[]
     */
    public function getCandidatos(): Collection
    {
        return $this->candidatos;
    }

    public function addCandidato(Candidatos $candidato): self
    {
        if (!$this->candidatos->contains($candidato)) {
            $this->candidatos[] = $candidato;
            $candidato->setEleicao($this);
        }

        return $this;
    }

    public function removeCandidato(Candidatos $candidato): self
    {
        if ($this->candidatos->contains($candidato)) {
            $this->candidatos->removeElement($candidato);
            // set the owning side to null (unless already changed)
            if ($candidato->getEleicao() === $this) {
                $candidato->setEleicao(null);
            }
        }

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
            $votos->setEleicao($this);
        }

        return $this;
    }

    public function removeVotos(Votos $votos): self
    {
        if ($this->votos->contains($votos)) {
            $this->votos->removeElement($votos);
            // set the owning side to null (unless already changed)
            if ($votos->getEleicao() === $this) {
                $votos->setEleicao(null);
            }
        }

        return $this;
    }

    public function getAtivo(): ?bool
    {
        return $this->ativo;
    }

    public function setAtivo(?bool $ativo): self
    {
        $this->ativo = $ativo;

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
        } else {
            //para atributos que não precisam de algoritimo para mascarar para a view
            //return $this->get();
            return $atrib . ' (View Error)';
        }
    }
}

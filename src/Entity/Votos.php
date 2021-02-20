<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VotosRepository")
 */
class Votos
{
    // id, criacao, criador, edicao, editor, eleicao, usuario, candidato
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Eleicoes", inversedBy="votos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $eleicao;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Usuarios", inversedBy="votos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $usuario;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Candidatos", inversedBy="votosEntitys")
     * @ORM\JoinColumn(nullable=false)
     */
    private $candidato;

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

    public function getCandidato(): ?Candidatos
    {
        return $this->candidato;
    }

    public function setCandidato(?Candidatos $candidato): self
    {
        $this->candidato = $candidato;

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
        } else {
            //para atributos que não precisam de algoritimo para mascarar para a view
            //return $this->get();
            return $atrib . ' (View Error)';
        }
    }
    
}

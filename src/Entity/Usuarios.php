<?php

namespace App\Entity;

use App\Services\Constants;
use App\Services\TextService;
use App\Validator\Constraints as CustomAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UsuariosRepository")
 */
class Usuarios
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
     * @ORM\ManyToOne(targetEntity="Logins")
     * @ORM\JoinColumn(nullable=false)
     */
    private $criador;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $edicao;

    /**
     * @ORM\ManyToOne(targetEntity="Logins")
     */
    private $editor;

    /**
     * @ORM\ManyToOne(targetEntity="Logins", inversedBy="usuarios")
     */
    private $login;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $nome;

    /**
     * @ORM\Column(type="string", length=255)
     * @CustomAssert\CpfCnpj
     */
    private $doc;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cargo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rg;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $matricula;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $nascimento;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $sexo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logradouro;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $enumero;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $complemento;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bairro;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $uf;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cidade;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cep;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $telefone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $whatsapp;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\OneToMany(targetEntity="Candidatos", mappedBy="usuario")
     */
    private $candidatos;

    /**
     * @ORM\OneToMany(targetEntity="Votos", mappedBy="usuario")
     */
    private $votos;


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

    public function getLogin(): ?Logins
    {
        return $this->login;
    }

    public function setLogin(?Logins $login): self
    {
        $this->login = $login;

        return $this;
    }
    
    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(string $nome): self
    {
        $this->nome = $nome;

        return $this;
    }

    public function getDoc(): ?string
    {
        return $this->doc;
    }

    public function setDoc(string $doc): self
    {
        $this->doc = $doc;

        return $this;
    }

    public function getCargo(): ?string
    {
        return $this->cargo;
    }

    public function setCargo(?string $cargo): self
    {
        $this->cargo = $cargo;

        return $this;
    }

    public function getRg(): ?string
    {
        return $this->rg;
    }

    public function setRg(?string $rg): self
    {
        $this->rg = $rg;

        return $this;
    }

    public function getMatricula(): ?string
    {
        return $this->matricula;
    }

    public function setMatricula(?string $matricula): self
    {
        $this->matricula = $matricula;

        return $this;
    }

    public function getNascimento(): ?\DateTimeInterface
    {
        return $this->nascimento;
    }

    public function setNascimento(?\DateTimeInterface $nascimento): self
    {
        $this->nascimento = $nascimento;

        return $this;
    }

    public function getSexo(): ?int
    {
        return $this->sexo;
    }

    public function setSexo(?int $sexo): self
    {
        $this->sexo = $sexo;

        return $this;
    }

    public function getLogradouro(): ?string
    {
        return $this->logradouro;
    }

    public function setLogradouro(?string $logradouro): self
    {
        $this->logradouro = $logradouro;

        return $this;
    }

    public function getEnumero(): ?string
    {
        return $this->enumero;
    }

    public function setEnumero(?string $enumero): self
    {
        $this->enumero = $enumero;

        return $this;
    }

    public function getComplemento(): ?string
    {
        return $this->complemento;
    }

    public function setComplemento(?string $complemento): self
    {
        $this->complemento = $complemento;

        return $this;
    }

    public function getBairro(): ?string
    {
        return $this->bairro;
    }

    public function setBairro(?string $bairro): self
    {
        $this->bairro = $bairro;

        return $this;
    }

    public function getUf(): ?string
    {
        return $this->uf;
    }

    public function setUf(?string $uf): self
    {
        $this->uf = $uf;

        return $this;
    }

    public function getCidade(): ?string
    {
        return $this->cidade;
    }

    public function setCidade(?string $cidade): self
    {
        $this->cidade = $cidade;

        return $this;
    }

    public function getCep(): ?string
    {
        return $this->cep;
    }

    public function setCep(?string $cep): self
    {
        $this->cep = $cep;

        return $this;
    }

    public function getTelefone(): ?string
    {
        return $this->telefone;
    }

    public function setTelefone(?string $telefone): self
    {
        $this->telefone = $telefone;

        return $this;
    }

    public function getWhatsapp(): ?string
    {
        return $this->whatsapp;
    }

    public function setWhatsapp(?string $whatsapp): self
    {
        $this->whatsapp = $whatsapp;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

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
            $candidato->setUsuario($this);
        }

        return $this;
    }

    public function removeCandidato(Candidatos $candidato): self
    {
        if ($this->candidatos->contains($candidato)) {
            $this->candidatos->removeElement($candidato);
            // set the owning side to null (unless already changed)
            if ($candidato->getUsuario() === $this) {
                $candidato->setUsuario(null);
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

    public function addVoto(Votos $voto): self
    {
        if (!$this->votos->contains($voto)) {
            $this->votos[] = $voto;
            $voto->setUsuario($this);
        }

        return $this;
    }

    public function removeVoto(Votos $voto): self
    {
        if ($this->votos->contains($voto)) {
            $this->votos->removeElement($voto);
            // set the owning side to null (unless already changed)
            if ($voto->getUsuario() === $this) {
                $voto->setUsuario(null);
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
        } else if ($atrib == "ramo_nome") {
            return $this->getRamo()?LojasMapper::ramos($this->getRamo()):'';
        } else if ($atrib == "nome_razao_social") {
            return $this->view('tipo_cpf?')?$this->getNome():($this->getRazaoSocial()?$this->getRazaoSocial():$this->getNome());
        } else if ($atrib == "identificacao_nome") {
            return $this->getIdentificacao()?$this->getIdentificacao():$this->view('nome_razao_social');
        } else if ($atrib == "tipo_cpf?") {
            return $this->getTipo() === 1?true:false;
        } else if ($atrib == "tipo_cnpj?") {
            return $this->getTipo() === 2?true:false;
        } else if ($atrib == "doc") {
            if(!$this->getDoc()) {
                return '';
            } else {
                return TextService::maskAsCpf($this->getDoc());
            } 
            return 'Tipo Indefinido';
        } else if ($atrib == "role_nome") {
            return $this->getRole()? Constants::colaboradoresRoles($this->getRole()):' - ';
        } else if ($atrib == "nascimento") {
            return $this->getNascimento()?$this->getNascimento()->format('d/m/Y'):'';
        } else if ($atrib == "sexo_nome") {
            return $this->getSexo()?Constants::pessoasSexos($this->getSexo()):'Ignorado';
        } else if ($atrib == "genero_nome") {
            return $this->getGenero()?Constants::pessoasSexos($this->getGenero()):'';
        } else if ($atrib == "estado_civil_nome") {
            return $this->getEstadoCivil()?Constants::pessoasEstadosCivis($this->getEstadoCivil()):'';
        } else if ($atrib == "endereco_linha") {
            $linha = $this->getLogradouro();
            if($this->getEnumero()) $linha.= ($linha?', ':'') . $this->getEnumero();
            if($this->getBairro()) $linha.= ($linha?', ':'') . $this->getBairro();
            if($this->getCidade()) $linha.= ($linha?' - ':'') . $this->getCidade();
            if($this->getUf()) $linha.= ($linha?' - ':'') . $this->getUf();
            if($this->getCep()) $linha.= ($linha?' CEP: ':'') . $this->getCep();
            if($this->getComplemento()) $linha.= ($linha?', ':'') . $this->getComplemento();
            return  $linha;
        } else if ($atrib == "tel_linha") {
            $ok = $this->getTel1();
            $ok.= $this->getTel1()?($this->getTel1Obs()?' [' . $this->getTel1Obs() . ']':''):'';
            $ok.= ($ok && $this->getTel2()?', ':'') . $this->getTel2();
            $ok.= $this->getTel2()?($this->getTel2Obs()?' [' . $this->getTel2Obs() . ']':''):'';
            $ok.= ($ok && $this->getTel3()?', ':'') . $this->getTel3();
            $ok.= $this->getTel3()?($this->getTel3Obs()?' [' . $this->getTel3Obs() . ']':''):'';
            $ok.= ($ok && $this->getTel4()?', ':'') . $this->getTel4();
            $ok.= $this->getTel4()?($this->getTel4Obs()?' [' . $this->getTel4Obs() . ']':''):'';
            return $ok;
        } else if ($atrib == "contato_linha") {
            $ok = $this->getContatoNome();
            if(!$ok)
                return '';
            $ok.= $this->getContatoCargo()?' (' . $this->getContatoCargo() . ')':'';
            $ok.= $this->getContatoTel()? ': ' . $this->getContatoTel():'';
            $ok.= $this->getContatoEmail()? ' - ' . $this->getContatoEmail():'';
            return  $ok;
        } else if ($atrib == "imagem_img") {
            if($this->getImagem()) {
                $src = $this->baseUrl . '/public_files' . $this->getImagem();
                return '<img class="img-responsive center-block" src="' . $src . '" class="img-responsive" alt="Foto do Colaborador" title="Foto do Colaborador"/>';
            } else {
                $src = $this->baseUrl . '/img/user_sem_imagem.jpg';
                return '<img class="img-thumbnail img-responsive center-block" src="' . $src . '" class="img-responsive" alt="Foto do Colaborador" title="Sem Imagem"/>';
            }
        } else if ($atrib == "imagem_thumbnail_img") {
            $max_height = '70'; // pixels
            $max_width = '70'; // pixels
            // img-rounded
            // img-circle
            // img-thumbnail
            if($this->getImagem()) {
                $src = $this->baseUrl . '/public_files' . $this->getImagem();
                return '<img class="img-thumbnail" src="' . $src . '" class="img-responsive" alt="Foto do Colaborador" title="Foto do Colaborador" style="max-height: ' . $max_height . 'px; max-width: ' . $max_width . 'px;"/>';
            } else {
                $src = $this->baseUrl . '/img/user_sem_imagem.jpg';
                return '<img class="img-thumbnail" src="' . $src . '" class="img-responsive" alt="Foto do Colaborador" title="Sem Imagem" style="max-height: ' . $max_height . 'px; max-width: ' . ($max_width - 20) . 'px;"/>';
            }
        } else {
            //para atributos que não precisam de algoritimo para mascarar para a view
            //return $this->get();
            return $atrib . ' (View Error)';
        }
    }
    
}

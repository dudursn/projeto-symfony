<?php

namespace App\Entity;

use App\Mapper\LoginsMapper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LoginsRepository")
 */
class Logins implements UserInterface
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
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Email
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pass;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nome;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $role;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $hash;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $hash_criacao;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $confirmado;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $ativo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token_trocar_senha;


    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $data_geracao_token;


    private $roles = [];

    /**
     * @ORM\OneToMany(targetEntity="Usuarios", mappedBy="login")
     */
    private $usuarios;

    public function __construct()
    {
        $this->usuarios = new ArrayCollection();
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

    public function getCriador(): ?self
    {
        return $this->criador;
    }

    public function setCriador(?self $criador): self
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

    public function getEditor(): ?self
    {
        return $this->editor;
    }

    public function setEditor(?self $editor): self
    {
        $this->editor = $editor;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPass(): ?string
    {
        return $this->pass;
    }

    public function setPass(?string $pass): self
    {
        $this->pass = $pass;

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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(?string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function getHashCriacao(): ?\DateTimeInterface
    {
        return $this->hash_criacao;
    }

    public function setHashCriacao(?\DateTimeInterface $hash_criacao): self
    {
        $this->hash_criacao = $hash_criacao;

        return $this;
    }

    public function getConfirmado(): ?bool
    {
        return $this->confirmado;
    }

    public function setConfirmado(?bool $confirmado): self
    {
        $this->confirmado = $confirmado;

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

    public function getTokenTrocarSenha(): ?string
    {
        return $this->token_trocar_senha;
    }

    public function setTokenTrocarSenha(?string $tokenTrocarSenha): self
    {
        $this->token_trocar_senha = $tokenTrocarSenha;

        return $this;
    }

    public function getDataGeracaoToken(): ?\DateTimeInterface
    {
        return $this->data_geracao_token;
    }

    public function setDataGeracaoToken(?\DateTimeInterface $dataGeracaoToken): self
    {
        $this->data_geracao_token = $dataGeracaoToken;

        return $this;
    }

    
    public function view($atrib, $data = null) {
        if ($atrib == "criacao") {
            return $this->getCriacao()?$this->getCriacao()->format('d/m/Y H:i:s'):'';
        } else if ($atrib == "hash_criacao") {
            return $this->getHashCriacao()?$this->getHashCriacao()->format('d/m/Y H:i:s'):'';
        } else if ($atrib == "edicao") {
            return $this->getEdicao()?$this->getEdicao()->format('d/m/Y H:i:s'):'';
        } else if ($atrib == "id-nome") {
            return Rtl_Utils::textToUrl($this->get('id').'-'.$this->get('nome'));
        } else if ($atrib == "role_nome") {
            return LoginsMapper::roles($this->getRole());
        } else if ($atrib == "confirmado_sim_nao") {
            return (int)$this->getConfirmado() === 1?'Sim':'Não';
        } else if ($atrib == "ativo_sim_nao") {
            return (int)$this->getAtivo() === 1?'Sim':'Não';
        } else if ($atrib == "ativo_label") {
            if($this->getAtivo())
                return '<span class="badge badge-primary" title="Acesso ao sistema está ativo">Ativo</span>';
            return '<span class="badge badge-danger" title="Acesso ao sistema está desativado">Desativado</span>';
        } else if ($atrib == "confirmado_label") {
            if($this->getConfirmado())
                return '<span class="badge badge-success" title="E-mail de acesso confirmado">Confirmado</span>';
            return '<span class="badge badge-warning" title="E-mail de acesso NÃO confirmado">Não Confirmado</span>';
        } else if ($atrib == "_____") {
            return '';
        } else if ($atrib == "_____") {
            return '';
        } else {
            //para atributos que não precisam de algoritimo para mascarar para a view
            //return $this->get();
            return $atrib . ' (View Error)';
        }
    }
    
    /*********************************************************************************************   USER INTERFACE   ***/
    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        if($this->roles && count($this->roles))
            return $this->roles;
        $this->setRoles(array('ROLE_USER'));
        $this->addRole($this->getRole());
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role): self
    {
        if($this->roles && count($this->roles))
            $this->roles[] = $role;
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->pass;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
    
    // @seeSerializablee::serialize()
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->email,
            $this->nome,
            $this->pass,
        ));
    }

    //@seeSerializablee::unserialize()
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->email,
            $this->nome,
            $this->pass,
        ) = unserialize($serialized, array('allowed_classes' => false));
    }
    
    /********************************************************************************************  / USER INTERFACE   ***/
    

    /**
     * @return Collection|Usuarios[]
     */
    public function getUsuarios(): Collection
    {
        return $this->usuarios;
    }

    public function addUsuario(Usuarios $usuario): self
    {
        if (!$this->usuarios->contains($usuario)) {
            $this->usuarios[] = $usuario;
            $usuario->setLogin($this);
        }

        return $this;
    }

    public function removeUsuario(Usuarios $usuario): self
    {
        if ($this->usuarios->contains($usuario)) {
            $this->usuarios->removeElement($usuario);
            // set the owning side to null (unless already changed)
            if ($usuario->getLogin() === $this) {
                $usuario->setLogin(null);
            }
        }

        return $this;
    }
}

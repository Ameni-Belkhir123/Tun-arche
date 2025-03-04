<?php
namespace App\Entity;

use App\Repository\CommantaireRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommantaireRepository::class)]
class Commantaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    private ?string $contenu = null;

    #[ORM\ManyToOne(targetEntity: Publication::class, inversedBy: 'commantaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Publication $id_pub = null;

    #[ORM\Column(type: 'integer')]
    private int $likes = 0;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $date;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?\App\Entity\User $user = null;

    // Constructor: initialize the date property
    public function __construct()
    {
        $this->date = new \DateTime();
    }

    // Getters and setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getIdPub(): ?Publication
    {
        return $this->id_pub;
    }

    public function setIdPub(?Publication $id_pub): self
    {
        $this->id_pub = $id_pub;
        return $this;
    }

    public function getLikes(): int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): self
    {
        $this->likes = $likes;
        return $this;
    }

    public function incrementLikes(): self
    {
        $this->likes++;
        return $this;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getUser(): ?\App\Entity\User
    {
        return $this->user;
    }

    public function setUser(?\App\Entity\User $user): self
    {
        $this->user = $user;
        return $this;
    }
}

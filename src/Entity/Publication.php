<?php

namespace App\Entity;

use App\Repository\PublicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PublicationRepository::class)]
class Publication
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le titre ne doit pas dépasser {{ limit }} caractères."
    )]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La description ne peut pas être vide.")]
    #[Assert\Length(
        min: 10,
        max: 255,
        minMessage: "La description doit contenir au moins {{ limit }} caractères.",
        maxMessage: "La description ne doit pas dépasser {{ limit }} caractères."
    )]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date de publication est obligatoire.")]
    #[Assert\Type(
        type: \DateTimeInterface::class,
        message: "La date doit être au format valide (YYYY-MM-DD)."
    )]
    private ?\DateTimeInterface $date_act = null;

    #[ORM\Column(type: 'integer')]
    private int $likes = 0;

    #[ORM\Column(type: 'integer')]
    private int $unlikes = 0;

    /**
     * @var Collection<int, Commantaire>
     */
    #[ORM\OneToMany(targetEntity: Commantaire::class, mappedBy: 'id_pub', cascade: ['remove'], orphanRemoval: true)]
    private Collection $commantaires;

    public function __construct()
    {
        $this->commantaires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getDateAct(): ?\DateTimeInterface
    {
        return $this->date_act;
    }

    public function setDateAct(\DateTimeInterface $date_act): static
    {
        $this->date_act = $date_act;
        return $this;
    }

    public function getLikes(): int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): static
    {
        $this->likes = $likes;
        return $this;
    }

    public function incrementLikes(): static
    {
        $this->likes++;
        return $this;
    }

    public function getUnlikes(): int
    {
        return $this->unlikes;
    }

    public function setUnlikes(int $unlikes): static
    {
        $this->unlikes = $unlikes;
        return $this;
    }

    public function incrementUnlikes(): static
    {
        $this->unlikes++;
        return $this;
    }

    /**
     * @return Collection<int, Commantaire>
     */
    public function getCommantaires(): Collection
    {
        return $this->commantaires;
    }

    public function addCommantaire(Commantaire $commantaire): static
    {
        if (!$this->commantaires->contains($commantaire)) {
            $this->commantaires->add($commantaire);
            $commantaire->setIdPub($this);
        }

        return $this;
    }

    public function removeCommantaire(Commantaire $commantaire): static
    {
        if ($this->commantaires->removeElement($commantaire)) {
            // set the owning side to null (unless already changed)
            if ($commantaire->getIdPub() === $this) {
                $commantaire->setIdPub(null);
            }
        }

        return $this;
    }
}
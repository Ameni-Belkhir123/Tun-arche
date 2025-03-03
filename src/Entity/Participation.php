<?php

namespace App\Entity;

use App\Repository\ParticipationRepository;
use App\Entity\Concours;
use App\Entity\User;
use App\Entity\Oeuvre;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParticipationRepository::class)]
#[ORM\UniqueConstraint(columns: ['concours_id', 'artist_id'])]
class Participation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Concours::class, inversedBy: 'participations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Concours $concours = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'artiste est obligatoire.")]
    private ?User $artist = null;

    #[ORM\ManyToOne(targetEntity: Oeuvre::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Oeuvre $oeuvre = null;

    #[ORM\Column]
    private int $nbrVotes = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $dateInscription;

    #[ORM\Column(name: 'image_path', type: 'text', nullable: true)]
    private ?string $imagePath = null;

    public function __construct()
    {
        $this->nbrVotes = 0;
        $this->dateInscription = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getConcours(): ?Concours { return $this->concours; }
    public function setConcours(?Concours $concours): self { $this->concours = $concours; return $this; }
    public function getArtist(): ?User { return $this->artist; }
    public function setArtist(?User $artist): self { $this->artist = $artist; return $this; }
    public function getOeuvre(): ?Oeuvre { return $this->oeuvre; }
    public function setOeuvre(?Oeuvre $oeuvre): self { $this->oeuvre = $oeuvre; return $this; }
    public function getNbrVotes(): int { return $this->nbrVotes; }
    public function setNbrVotes(int $nbrVotes): self { $this->nbrVotes = $nbrVotes; return $this; }
    public function incrementVotes(): void { $this->nbrVotes++; }
    public function getDateInscription(): \DateTimeInterface { return $this->dateInscription; }
    public function setDateInscription(\DateTimeInterface $dateInscription): self { $this->dateInscription = $dateInscription; return $this; }
    public function getImagePath(): ?string { return $this->imagePath; }
    public function setImagePath(?string $imagePath): self { $this->imagePath = $imagePath; return $this; }
    public function decrementVotes(): void
    {
        if ($this->nbrVotes > 0) {
            $this->nbrVotes--;
        }
    }


}

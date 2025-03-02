<?php

namespace App\Entity;

use App\Repository\EventRepository;
use App\Entity\Billet;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name_event = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual("today")]
    private ?\DateTimeInterface $date_start = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank]
    #[Assert\GreaterThan(propertyPath: "date_start")]
    private ?\DateTimeInterface $date_end = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $place_event = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $discription = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Type("numeric")]
    #[Assert\Range(min: 1, max: 499)]
    private ?int $price = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?int $totalTickets = null;

    #[ORM\Column]
    private ?int $soldTickets = null;

    #[ORM\OneToMany(targetEntity: Billet::class, mappedBy: 'event')]
    private Collection $billets;

    public function __construct()
    {
        $this->soldTickets = 0;
        $this->billets = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getNameEvent(): ?string { return $this->name_event; }
    public function setNameEvent(?string $name_event): self { $this->name_event = $name_event; return $this; }
    public function getDateStart(): ?\DateTimeInterface { return $this->date_start; }
    public function setDateStart(?\DateTimeInterface $date_start): self { $this->date_start = $date_start; return $this; }
    public function getDateEnd(): ?\DateTimeInterface { return $this->date_end; }
    public function setDateEnd(?\DateTimeInterface $date_end): self { $this->date_end = $date_end; return $this; }
    public function getPlaceEvent(): ?string { return $this->place_event; }
    public function setPlaceEvent(?string $place_event): self { $this->place_event = $place_event; return $this; }
    public function getDiscription(): ?string { return $this->discription; }
    public function setDiscription(?string $discription): self { $this->discription = $discription; return $this; }
    public function getPrice(): ?int { return $this->price; }
    public function setPrice(?int $price): self { $this->price = $price; return $this; }
    public function getTotalTickets(): ?int { return $this->totalTickets; }
    public function setTotalTickets(?int $totalTickets): self { $this->totalTickets = $totalTickets; return $this; }
    public function getSoldTickets(): ?int { return $this->soldTickets; }
    public function setSoldTickets(?int $soldTickets): self { $this->soldTickets = $soldTickets; return $this; }
    public function getBillets(): Collection { return $this->billets; }
    public function addBillet(Billet $billet): self { if(!$this->billets->contains($billet)){ $this->billets->add($billet); $billet->setEvent($this); } return $this; }
    public function removeBillet(Billet $billet): self { if($this->billets->removeElement($billet)){ if($billet->getEvent() === $this){ $billet->setEvent(null); } } return $this; }
}

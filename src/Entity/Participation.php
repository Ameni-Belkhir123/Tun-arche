<?php

namespace App\Entity;

use App\Repository\ParticipationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParticipationRepository::class)]
class Participation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(nullable: false)]
    #[Assert\NotBlank(message: "Le nombre de votes ne peut pas être vide.")]
    #[Assert\PositiveOrZero(message: "Le nombre de votes doit être un nombre positif ou zéro.")]
    private ?int $nbrvotes = null;
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    #[Assert\NotBlank(message: "La date d'inscription est requise.")]
    #[Assert\Type(\DateTimeInterface::class, message: "La date d'inscription doit être une date valide.")]
    private ?\DateTimeInterface $date_inscription = null;
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getNbrVotes(): ?int
    {
        return $this->nbrvotes;
    }
    public function setNbrVotes(?int $nbr_votes): static
    {
        $this->nbrvotes = $nbr_votes;
        return $this;
    }
    public function getDateInscription(): ?\DateTimeInterface
    {
        return $this->date_inscription;
    }
    public function setDateInscription(?\DateTimeInterface $date_inscription): static
    {
        $this->date_inscription = $date_inscription;
        return $this;
    }
}

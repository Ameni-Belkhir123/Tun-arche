<?php
// src/Entity/Evaluation.php

namespace App\Entity;

use App\Repository\EvaluationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EvaluationRepository::class)]
class Evaluation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 500, maxMessage: "Le commentaire ne doit pas dépasser 500 caractères.")]
    private ?string $commentaire = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank(message: "La note est obligatoire.")]
    #[Assert\Range(min: 0, max: 20, notInRangeMessage: "La note doit être entre 0 et 5.")]
    private ?int $note = null;

    #[ORM\ManyToOne(targetEntity: Formation::class, inversedBy: 'evaluation')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Formation $formation = null;

    #[ORM\ManyToOne(targetEntity: \App\Entity\User::class, inversedBy: 'evaluations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?\App\Entity\User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(?int $note): static
    {
        $this->note = $note;
        return $this;
    }

    public function getFormation(): ?Formation
    {
        return $this->formation;
    }

    public function setFormation(?Formation $formation): static
    {
        $this->formation = $formation;
        return $this;
    }

    public function getUser(): ?\App\Entity\User
    {
        return $this->user;
    }

    public function setUser(?\App\Entity\User $user): static
    {
        $this->user = $user;
        return $this;
    }
}

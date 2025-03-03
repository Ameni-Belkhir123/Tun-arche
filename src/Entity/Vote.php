<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\VoteRepository;

#[ORM\Entity(repositoryClass: VoteRepository::class)]
#[ORM\Table(name: 'vote', uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'uniq_user_concours', columns: ['user_id', 'concours_id'])
])]
class Vote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: \App\Entity\User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private \App\Entity\User $user;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Participation::class)]
    #[ORM\JoinColumn(nullable: false)]
    private \App\Entity\Participation $participation;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Concours::class)]
    #[ORM\JoinColumn(nullable: false)]
    private \App\Entity\Concours $concours;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): \App\Entity\User
    {
        return $this->user;
    }

    public function setUser(\App\Entity\User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getParticipation(): \App\Entity\Participation
    {
        return $this->participation;
    }

    public function setParticipation(\App\Entity\Participation $participation): self
    {
        $this->participation = $participation;
        return $this;
    }

    public function getConcours(): \App\Entity\Concours
    {
        return $this->concours;
    }

    public function setConcours(\App\Entity\Concours $concours): self
    {
        $this->concours = $concours;
        return $this;
    }
}

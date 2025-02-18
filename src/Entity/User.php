<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Length(min: 2, max: 50, minMessage: "Le nom doit avoir au moins 2 caractères.")]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire.")]
    #[Assert\Length(min: 2, max: 50, minMessage: "Le prénom doit avoir au moins 2 caractères.")]
    private ?string $last_name = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide.")]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le mot de passe est obligatoire.")]
    #[Assert\Length(min: 6, max: 255, minMessage: "Le mot de passe doit contenir au moins 6 caractères.")]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $role = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(?string $last_name): static
    {
        $this->last_name = $last_name;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): static
    {
        $this->role = $role;
        return $this;
    }


    public function getRoles(): array
    {
        $roleMapping = [
            'artist' => 'ROLE_ARTIST',
            'user' => 'ROLE_USER',
            'admin'   => 'ROLE_ADMIN',
        ];
    
        $roles = [$roleMapping[$this->role] ?? 'ROLE_USER']; // Mapping du rôle stocké
    
        if (!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }
        
        return $roles;
    }
    
    
        public function eraseCredentials(): void
        {
            // Symfony demande cette méthode, mais elle peut être vide
        }
        public function getUser($credentials, UserProviderInterface $userProvider)
        {
            $token = new CsrfToken('authenticate', $credentials['csrf_token']);
            if (!$this->csrfTokenManager->isTokenValid($token)) {
                throw new InvalidCsrfTokenException();
            }
        
            // Debugging: Log the submitted email
            dump('Submitted Email:', $credentials['email']);
        
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);
        
            // Debugging: Log the retrieved user
            dump('Retrieved User:', $user);
        
            if (!$user) {
                throw new CustomUserMessageAuthenticationException('Email could not be found.');
            }
        
            return $user;
        }
        public function getUserIdentifier(): string
        {
            return $this->email;
        }
        
}

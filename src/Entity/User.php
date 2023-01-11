<?php

namespace App\Entity;

use App\Entity\Common\DatedInterface;
use App\Entity\Common\DatedTrait;
use App\Entity\Common\IdInterface;
use App\Entity\Common\IdTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table("`user`")
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ApiResource(
 *      normalizationContext={"groups"={"users_read"}},
 *     itemOperations={
 *          "post"={
 *              "name"="signup",
                "uriTemplate"="/api/signup",
                "controller"=AuthController::class
 *          }
 *     }
 * )
 * @UniqueEntity(fields = {"email"},message ="Un utilisateur ayant cette adresse email existe déjà")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface, DatedInterface, IdInterface
{
    use DatedTrait;
    use IdTrait;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Groups({"users_read"})
     * @Assert\NotBlank(message="L'adresse email de l'utilisateur est obligatoire")
     * @Assert\Email(message="Le format de l'adresse email doit être valide")
     */
    private $email = null;

    /**
     * @ORM\Column
     * @Groups({"users_read"})
     */
    private ?array $roles = [];

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"users_read"})
     * @Assert\NotBlank(message="Le prénom du customer est obligatoire")
     * @Assert\Length(
     *  allowEmptyString =true,
     *  min=3, minMessage="Le prénom doit faire entre 3 et 255 caractères",
     *  max=255, maxMessage="Le prénom doit faire entre 3 et 255 caractères"
     * )
     */
    private $firstname = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"users_read"})
     * @Assert\NotBlank(message="Le nom du customer est obligatoire")
     * @Assert\Length(
     *  allowEmptyString =true,
     *  min=3, minMessage="Le nom doit faire entre 3 et 255 caractères",
     *  max=255, maxMessage="Le nom doit faire entre 3 et 255 caractères"
     * )
     */
    private $lastname = null;

    /**
     * @ORM\Column
     * @Groups({"users_read"})
     */
    private $phone = null;

    /**
     * @ORM\Column
     * @Groups({"users_read"})
     */
    private $address = null;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="Le mot de passe est obligatoire")
     */
    private $password = null;

    /**
     * @ORM\OneToOne(inversedBy="user", targetEntity=Account::class, cascade={"persist", "remove"})
     * @Groups({"users_read"})
     */
    private $account = null;

    public function __construct() {
        $this->createdAt = new \DateTime();
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
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
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return string|null
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * @param string|null $firstname
     */
    public function setFirstname(?string $firstname): void
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string|null
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * @param string|null $lastname
     */
    public function setLastname(?string $lastname): void
    {
        $this->lastname = $lastname;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     */
    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     */
    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    /**
     * @return Account|null
     */
    public function getAccount(): ?Account
    {
        return $this->account;
    }

    /**
     * @param Account|null $account
     */
    public function setAccount(?Account $account): void
    {
        $this->account = $account;
    }
}

<?php

namespace App\Entity;

use App\Repository\ProspectRepository;
use App\Entity\Common\DatedInterface;
use App\Entity\Common\DatedTrait;
use App\Entity\Common\IdInterface;
use App\Entity\Common\IdTrait;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\Api\ProspectController;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table("`prospect`")
 * @ORM\Entity(repositoryClass=ProspectRepository::class)
 * @ApiResource(
 *      normalizationContext={"groups"={"prospect_read"}},
 *     collectionOperations={
 *          "post"={
 *              "name"="create",
 *              "controller"=ProspectController::class
 *          }, "get"
 *     }
 * )
 * @UniqueEntity(fields = {"email"},message ="Un prospect ayant cette adresse email existe déjà")
 */
class Prospect implements DatedInterface, IdInterface
{
    use DatedTrait;
    use IdTrait;

    /**
     * @ORM\ManyToOne(inversedBy="prospects", targetEntity=Account::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"prospect_read"})
     */
    private $commercial = null;

    /**
     * @ORM\Column(length=255)
     * @Groups({"prospect_read"})
     * @Assert\NotBlank(message="Le prénom du prospect est obligatoire")
     * @Assert\Length(
     *  allowEmptyString =true,
     *  min=3, minMessage="Le prénom doit faire entre 3 et 255 caractères",
     *  max=255, maxMessage="Le prénom doit faire entre 3 et 255 caractères"
     * )
     */
    private $firstname = null;

    /**
     * @ORM\Column(length=255)
     * @Groups({"prospect_read"})
     * @Assert\NotBlank(message="Le nom du prospect est obligatoire")
     * @Assert\Length(
     *  allowEmptyString =true,
     *  min=3, minMessage="Le nom doit faire entre 3 et 255 caractères",
     *  max=255, maxMessage="Le nom doit faire entre 3 et 255 caractères"
     * )
     */
    private $lastname = null;

    /**
     * @ORM\Column(length=255)
     * @Groups({"prospect_read"})
     * @Assert\NotBlank(message="L'adresse email du prospect est obligatoire")
     * @Assert\Email(message="Le format de l'adresse email doit être valide")
     */
    private $email = null;

    /**
     * @ORM\Column(length=255, nullable=true)
     * @Groups({"prospect_read"})
     */
    private $phone = null;

    public function __construct() {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommercial(): ?Account
    {
        return $this->commercial;
    }

    public function setCommercial(?Account $commercial): self
    {
        $this->commercial = $commercial;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getInfos(): array
    {
        return [
            'id' => $this->getId(),
            'commercial' => $this->getCommercial()->getUser(),
            'phone' => $this->getPhone(),
            'email' => $this->getEmail(),
            'firstname' => $this->getFirstname(),
            'lastname' => $this->getLastname(),
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt()
        ];
    }
}

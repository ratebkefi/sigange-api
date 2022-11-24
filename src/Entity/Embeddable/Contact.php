<?php

namespace App\Entity\Embeddable;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Embeddable()
 */
class Contact
{

    public const GROUP_READ = 'contact:read';
    public const GROUP_WRITE = 'contact:write';


    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     * @Groups({Contact::GROUP_READ, Contact::GROUP_WRITE})
     * // TODO regex 'm' or 'f'?
     * @Assert\Length(
     *     min=1,
     *     max=1,
     *     exactMessage="Maximum number of characters is 1",
     * )
     */
    private $gender;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({Contact::GROUP_READ, Contact::GROUP_WRITE})
     * @Assert\Length(
     *     min=1,
     *     max=100,
     *     maxMessage="Maximum number of characters is 100",
     *     minMessage="Minimum number of characters is 1"
     * )
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({Contact::GROUP_READ, Contact::GROUP_WRITE})
     * @Assert\Length(
     *     min=1,
     *     max=100,
     *     maxMessage="Maximum number of characters is 100",
     *     minMessage="Minimum number of characters is 1"
     * )
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({Contact::GROUP_READ, Contact::GROUP_WRITE})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     maxMessage="Maximum number of characters is 255",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Groups({Contact::GROUP_READ, Contact::GROUP_WRITE})
     * @Assert\Length(
     *     min=2,
     *     max=20,
     *     maxMessage="Maximum number of characters is 20",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $phone;

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
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
}

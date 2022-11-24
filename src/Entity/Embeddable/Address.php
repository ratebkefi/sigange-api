<?php

namespace App\Entity\Embeddable;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Embeddable()
 */
class Address
{
    public const GROUP_READ = 'address:read';
    public const GROUP_WRITE = 'address:write';
    
    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({Address::GROUP_READ, Address::GROUP_WRITE})
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     maxMessage="Maximum number of characters is 50",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({Address::GROUP_READ, Address::GROUP_WRITE})
     * @Assert\Length(
     *     min=2,
     *     max=100,
     *     maxMessage="Maximum number of characters is 100",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups({Address::GROUP_READ, Address::GROUP_WRITE})
     * @Assert\Length(
     *     min=2,
     *     max=10,
     *     maxMessage="Maximum number of characters is 10",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $zipCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({Address::GROUP_READ, Address::GROUP_WRITE})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     maxMessage="Maximum number of characters is 255",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $street;

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    public function getZipCode()
    {
        return $this->zipCode;
    }

    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;
        return $this;
    }

    public function getStreet()
    {
        return $this->street;
    }

    public function setStreet($street)
    {
        $this->street = $street;
        return $this;
    }
}

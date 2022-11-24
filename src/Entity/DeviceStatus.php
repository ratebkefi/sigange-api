<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;

use App\Repository\DeviceStatusRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Device Status
 * @ApiFilter(PropertyFilter::class)
 * @ApiResource(
 *     normalizationContext=DeviceStatus::API_READ,
 *     collectionOperations={
 *         "get"={"security"="is_granted('ROLE_DEVICE_STATUS_GET_COLLECTION')"},
 *     },
 *     itemOperations={
 *         "get"={"normalization_context"=DeviceStatus::API_READ_DETAIL, "security"="is_granted('ROLE_DEVICE_STATUS_GET_ITEM', object)"},
 *     }
 * )
 * @ORM\Entity(repositoryClass=DeviceStatusRepository::class)
 * @UniqueEntity("code")
 */
class DeviceStatus
{
    public const UUID_NAMESPACE = '57e346cb-b5f9-4d59-8418-9fac3cfee309';

    public const GROUP_READ_DEFAULT = 'device_status:read_default';
    public const GROUP_READ_ITEM = 'device_status:read_item';

    public const API_READ = [
        'swagger_definition_name' => 'Read',
        'groups' => [
            DeviceStatus::GROUP_READ_DEFAULT,
        ],
    ];
    public const API_READ_DETAIL = [
        'swagger_definition_name' => 'Detail',
        'groups' => [
            DeviceStatus::GROUP_READ_DEFAULT,
            DeviceStatus::GROUP_READ_ITEM,
        ],
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=false)
     */
    private $id;

    /**
     * @var Uuid External identifier used by API
     *
     * @ORM\Column(type="uuid", unique=true)
     * @ApiProperty(identifier=true)
     * @Groups({DeviceStatus::GROUP_READ_DEFAULT})
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_PARTIAL)
     * @Groups({DeviceStatus::GROUP_READ_DEFAULT})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     maxMessage="Maximum number of characters is 255",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({DeviceStatus::GROUP_READ_DEFAULT})
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="create")
     * @Groups({DeviceStatus::GROUP_READ_DEFAULT})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="update")
     * @Groups({DeviceStatus::GROUP_READ_DEFAULT})
     */
    private $updatedAt;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?Uuid
    {
        return $this->code;
    }

    public function setCode(Uuid $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }


}

<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

use App\Repository\DeviceModelOutputRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Device Model video output
 * @ApiFilter(PropertyFilter::class)
 * @ApiResource(
 *     normalizationContext=DeviceModelOutput::API_READ,
 *     denormalizationContext=DeviceModelOutput::API_UPDATE,
 *     collectionOperations={
 *         "get"={"security"="is_granted('ROLE_DEVICE_MODEL_OUTPUT_GET_COLLECTION')"},
 *         "post"={"denormalization_context"=DeviceModelOutput::API_CREATE,
 *                 "security_post_denormalize" = "is_granted('ROLE_DEVICE_MODEL_OUTPUT_POST_COLLECTION', object)"}
 *     },
 *     itemOperations={
 *         "get"={"normalization_context"=DeviceModelOutput::API_READ_DETAIL, "security"="is_granted('ROLE_DEVICE_MODEL_OUTPUT_GET_ITEM', object)"},
 *         "delete"={"security"="is_granted('ROLE_DEVICE_MODEL_OUTPUT_DELETE_ITEM', object)"},
 *         "put"={"denormalization_context"=DeviceModelOutput::API_UPDATE, "security"="is_granted('ROLE_DEVICE_MODEL_OUTPUT_PUT_ITEM', object)"},
 *         "patch"={"security"="is_granted('ROLE_DEVICE_MODEL_OUTPUT_PATCH_ITEM', object)"}
 *     }
 * )
 * @ORM\Entity(repositoryClass=DeviceModelOutputRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(columns={"model_id", "number"})
 *     }
 * )
 * @UniqueEntity("code")
 * @UniqueEntity(
 *     fields={"number", "model"},
 *     message="This number is already in use with that Model."
 * )
 *
 */
class DeviceModelOutput
{
    public const GROUP_CREATE = 'device_model_output:create';
    public const GROUP_READ_DEFAULT = 'device_model_output:read_default';
    public const GROUP_READ_COLLECTION = 'device_model_output:read_list';
    public const GROUP_READ_ITEM = 'device_model_output:read_detail';
    public const GROUP_UPDATE = 'device_model_output:write';
    public const GROUP_READ_MINIMAL = 'device_model_output:read_minimal';

    public const API_CREATE = [
        'swagger_definition_name' => 'Create',
        'groups' => [
            DeviceModelOutput::GROUP_CREATE,
            DeviceModelOutput::GROUP_UPDATE,
        ],
    ];
    public const API_READ = [
        'swagger_definition_name' => 'Read',
        'groups' => [
            DeviceModelOutput::GROUP_READ_DEFAULT,
            DeviceModelOutput::GROUP_READ_COLLECTION,
        ],
    ];
    public const API_READ_DETAIL = [
        'swagger_definition_name' => 'Detail',
        'groups' => [
            DeviceModelOutput::GROUP_READ_DEFAULT,
            DeviceModelOutput::GROUP_READ_COLLECTION,
            DeviceModelOutput::GROUP_READ_ITEM,
        ],
    ];
    public const API_READ_MINIMAL = [
        'swagger_definition_name' => 'Read minimal data',
        'groups' => [
            DeviceModelOutput::GROUP_READ_DEFAULT,
            DeviceModelOutput::GROUP_READ_COLLECTION,
        ],
    ];
    public const API_UPDATE = [
        'swagger_definition_name' => 'Update',
        'groups' => [
            DeviceModelOutput::GROUP_UPDATE,
        ],
    ];

    /**
     * @var int Internal identifier used for database foreign key
     *
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
     * @Groups({DeviceModelOutput::GROUP_READ_DEFAULT, DeviceModelOutput::GROUP_CREATE})
     */
    private $code;

    /**
     * @var string Name of the output
     *
     * @ORM\Column(type="string", length=255)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_PARTIAL)
     * @Groups({DeviceModelOutput::GROUP_READ_DEFAULT, DeviceModelOutput::GROUP_UPDATE, DeviceModelOutput::GROUP_READ_MINIMAL})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     maxMessage="Maximum number of characters is 255",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $name;

    /**
     * @var string Description of the output
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({DeviceModelOutput::GROUP_READ_DEFAULT, DeviceModelOutput::GROUP_UPDATE})
     */
    private $description;

    /**
     * @var DeviceModel Device model of the output
     *
     * @ORM\ManyToOne(targetEntity=DeviceModel::class, inversedBy="outputs")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({DeviceModelOutput::GROUP_READ_DEFAULT, DeviceModelOutput::GROUP_CREATE})
     */
    private $model;

    /**
     * @var int Number of the output in the device model (order)
     * @ORM\Column(type="integer")
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_EXACT)
     * @Groups({DeviceModelOutput::GROUP_READ_DEFAULT, DeviceModelOutput::GROUP_UPDATE, DeviceModelOutput::GROUP_READ_MINIMAL})
     */
    private $number;

    /**
     * @var \DateTime Creation date
     *
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="create")
     * @Groups({DeviceModelOutput::GROUP_READ_DEFAULT})
     */
    private $createdAt;

    /**
     * @var \DateTime Last update date
     *
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="update")
     * @Groups({DeviceModelOutput::GROUP_READ_DEFAULT})
     */
    private $updatedAt;

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

    public function getModel(): ?DeviceModel
    {
        return $this->model;
    }

    public function setModel(?DeviceModel $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

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

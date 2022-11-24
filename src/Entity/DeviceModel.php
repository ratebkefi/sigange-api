<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;

use App\Repository\DeviceModelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Device Model
 *
 * @ApiFilter(PropertyFilter::class)
 *  @ApiFilter (OrderFilter::class, properties={
 *     "code",
 *     "name",
 *     "createdAt",
 *     "updatedAt"
 *     })
 * @ApiResource(
 *     normalizationContext=DeviceModel::API_READ,
 *     denormalizationContext=DeviceModel::API_UPDATE,
 *     collectionOperations={
 *         "get"={"security"="is_granted('ROLE_DEVICE_MODEL_GET_COLLECTION')"},
 *         "post"={"denormalization_context"=DeviceModel::API_CREATE,
 *                 "security_post_denormalize" = "is_granted('ROLE_DEVICE_MODEL_POST_COLLECTION', object)"}
 *     },
 *     itemOperations={
 *         "get"={"normalization_context"=DeviceModel::API_READ_DETAIL, "security"="is_granted('ROLE_DEVICE_MODEL_GET_ITEM', object)"},
 *         "delete"= {"security"="is_granted('ROLE_DEVICE_MODEL_DELETE_ITEM', object)"},
 *         "put"={"denormalization_context"=DeviceModel::API_UPDATE, "security"="is_granted('ROLE_DEVICE_MODEL_PUT_ITEM', object)"},
 *         "patch"={"security"="is_granted('ROLE_DEVICE_MODEL_PATCH_ITEM', object)"},
 *     }
 * )
 * @ORM\Entity(repositoryClass=DeviceModelRepository::class)
 * @UniqueEntity("code")
 */
class DeviceModel
{
    public const GROUP_CREATE = 'device_model:create';
    public const GROUP_READ_DEFAULT = 'device_model:read_default';
    public const GROUP_READ_COLLECTION = 'device_model:read_list';
    public const GROUP_READ_ITEM = 'device_model:read_detail';
    public const GROUP_UPDATE = 'device_model:write';

    public const API_CREATE = [
        'swagger_definition_name' => 'Create',
        'groups' => [
            DeviceModel::GROUP_CREATE,
            DeviceModel::GROUP_UPDATE,
            DeviceModelOutput::GROUP_CREATE,
            DeviceModelOutput::GROUP_UPDATE,
        ],
    ];
    public const API_READ = [
        'swagger_definition_name' => 'Read',
        'groups' => [
            DeviceModel::GROUP_READ_DEFAULT,
            DeviceModel::GROUP_READ_COLLECTION,
            DeviceModelOutput::GROUP_READ_DEFAULT,
        ],
    ];
    public const API_READ_DETAIL = [
        'swagger_definition_name' => 'Detail',
        'groups' => [
            DeviceModel::GROUP_READ_DEFAULT,
            DeviceModel::GROUP_READ_COLLECTION,
            DeviceModel::GROUP_READ_ITEM,
            DeviceModelOutput::GROUP_READ_DEFAULT,
            DeviceModelOutput::GROUP_READ_COLLECTION,
            DeviceModelOutput::GROUP_READ_ITEM,
        ],
    ];
    public const API_UPDATE = [
        'swagger_definition_name' => 'Update',
        'groups' => [
            DeviceModel::GROUP_UPDATE,
            DeviceModelOutput::GROUP_CREATE,
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
     * @Groups({DeviceModel::GROUP_READ_DEFAULT, DeviceModel::GROUP_CREATE})
     */
    private $code;

    /**
     * @var string Name of the device model
     *
     * @ORM\Column(type="string", length=255)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_PARTIAL)
     * @Groups({DeviceModel::GROUP_READ_DEFAULT, DeviceModel::GROUP_UPDATE})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     maxMessage="Maximum number of characters is 255",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $name;

    /**
     * @var string Description of the model device
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({DeviceModel::GROUP_READ_DEFAULT, DeviceModel::GROUP_UPDATE})
     */
    private $description;

    /**
     * @var Collection|DeviceModelOutput[] List of device model outputs
     *
     * @ORM\OneToMany(targetEntity=DeviceModelOutput::class, mappedBy="model", orphanRemoval=true,cascade={"persist"})
     * @ORM\OrderBy({"number" = "ASC"})
     * @Groups({DeviceModel::GROUP_READ_DEFAULT, DeviceModel::GROUP_UPDATE})
     */
    private $outputs;

    /**
     * @var \DateTime Creation date
     *
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="create")
     * @Groups({DeviceModel::GROUP_READ_DEFAULT})
     */
    private $createdAt;

    /**
     * @var \DateTime Last update date
     *
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="update")
     * @Groups({DeviceModel::GROUP_READ_DEFAULT})
     */
    private $updatedAt;

    public function __construct()
    {
        $this->outputs = new ArrayCollection();
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

    /**
     * @return Collection|DeviceModelOutput[]
     */
    public function getOutputs(): Collection
    {
        return $this->outputs;
    }

    public function setOutputs($outputs): self
    {
        foreach ($outputs as $output) {
            $this->addDeviceModelOutput($output);
        }
        return $this;
    }

    public function addDeviceModelOutput(DeviceModelOutput $deviceModelOutput): self
    {
        if (!$this->outputs->contains($deviceModelOutput)) {
            $this->outputs[] = $deviceModelOutput;
            $deviceModelOutput->setModel($this);
        }

        return $this;
    }

    public function removeDeviceModelOutput(DeviceModelOutput $deviceModelOutput): self
    {
        if ($this->outputs->removeElement($deviceModelOutput)) {
            // set the owning side to null (unless already changed)
            if ($deviceModelOutput->getModel() === $this) {
                $deviceModelOutput->setModel(null);
            }
        }

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

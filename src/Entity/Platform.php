<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Controller\PlatformCustomController;
use App\Interfaces\UserGroupVisibilityInterface;
use App\Repository\PlatformRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Platform
 * @ApiFilter(PropertyFilter::class)
 * @ApiFilter (OrderFilter::class, properties={
 *     "code",
 *     "name",
 *     "userGroup.name",
 *     "apiHttpUrl",
 *     "apiHttpProxy",
 *     "apiSocketUrl",
 *     "createdAt",
 *     "updatedAt"
 *     })
 * @ApiResource(
 *     normalizationContext=Platform::API_READ,
 *     denormalizationContext=Platform::API_UPDATE,
 *     collectionOperations={
 *         "get"={"security"="is_granted('ROLE_PLATFORM_GET_COLLECTION')"},
 *         "post"={"denormalization_context"=Platform::API_CREATE,
 *                 "security_post_denormalize" = "is_granted('ROLE_PLATFORM_POST_COLLECTION', object)"}
 *     },
 *     itemOperations={
 *         "get"={"normalization_context"=Platform::API_READ_DETAIL, "security"="is_granted('ROLE_PLATFORM_GET_ITEM', object)"},
 *         "delete"={"security"="is_granted('ROLE_PLATFORM_DELETE_ITEM', object)"},
 *         "put"={"denormalization_context"=Platform::API_UPDATE, "security"="is_granted('ROLE_PLATFORM_PUT_ITEM', object)"},
 *         "patch"= {"security"="is_granted('ROLE_PLATFORM_PATCH_ITEM', object)"},
 *         "api_platforms_patch_enable" = {
 *             "route_name"="api_platforms_patch_enable",
 *             "controller"=PlatformCustomController::class,
 *             "method"="PATCH",
 *             "path"="/platforms/{code}/enable",
 *             "denormalization_context"= Platform::API_PATCH_ENABLED,
 *             "normalization_context"= Platform::API_PATCH_ENABLED,
 *             "openapi_context" = {
 *                 "summary" = "Set the Platform as enabled true",
 *                 "description" = "Updates only the property **enabled** of a Platform and set it to `true`."
 *             }
 *         },
 *         "api_platforms_patch_disable" = {
 *             "route_name"="api_platforms_patch_disable",
 *             "controller"=PlatformCustomController::class,
 *             "method"="PATCH",
 *             "path"="/platforms/{code}/disable",
 *             "normalization_context"= Platform::API_PATCH_DISABLED,
 *             "openapi_context" = {
 *                 "summary" = "Set the Platform as enabled false",
 *                 "description" = "Updates only the property **enabled** of a Platform and set it to `false`."
 *             }
 *         },
 *         "patch_name" = {
 *             "method"="PATCH",
 *             "path"="/platforms/{code}/name",
 *             "format"= "jsonld",
 *             "security"="is_granted('ROLE_PLATFORM_PATCH_NAME')",
 *             "denormalization_context"= Platform::API_PATCH_NAME,
 *             "normalization_context"= Platform::API_PATCH_NAME,
 *             "openapi_context" = {
 *                 "summary" = "Updates only the name and description of a Platform",
 *                 "description" = "Updates only the  **name** and the **description** of a Platform. Both fields don't need to be filled at the same time"
 *             }
 *         },
 *     }
 * )
 * @ORM\Entity(repositoryClass=PlatformRepository::class)
 * @UniqueEntity("code")
 */
class Platform implements UserGroupVisibilityInterface
{
    public const GROUP_CREATE = 'platform:create';
    public const GROUP_READ_DEFAULT = 'platform:read_default';
    public const GROUP_READ_COLLECTION = 'platform:read_list';
    public const GROUP_READ_ITEM = 'platform:read_detail';
    public const GROUP_UPDATE = 'platform:write';
    public const GROUP_UPDATE_ENABLED = 'platform:write_enabled';
    public const GROUP_UPDATE_DISABLED = 'platform:write_disabled';
    public const GROUP_UPDATE_NAME = 'platform:write_name';

    public const API_CREATE = [
        'swagger_definition_name' => 'Create',
        'groups' => [
            Platform::GROUP_CREATE,
            Platform::GROUP_UPDATE,
        ],
    ];
    public const API_READ = [
        'swagger_definition_name' => 'Read',
        'groups' => [
            Platform::GROUP_READ_DEFAULT,
            Platform::GROUP_READ_COLLECTION,
            UserGroup::GROUP_READ_DEFAULT,
        ],
    ];
    public const API_READ_DETAIL = [
        'swagger_definition_name' => 'Details',
        'groups' => [
            Platform::GROUP_READ_DEFAULT,
            Platform::GROUP_READ_COLLECTION,
            Platform::GROUP_READ_ITEM,
            UserGroup::GROUP_READ_DEFAULT,
            TemplateModel::GROUP_READ_DEFAULT,
            TemplateModelOutput::GROUP_READ_DEFAULT,
            VideoStream::GROUP_READ_DEFAULT,
            VideoOverlay::GROUP_READ_DEFAULT,
        ],
    ];
    public const API_UPDATE = [
        'swagger_definition_name' => 'Update',
        'groups' => [
            Platform::GROUP_UPDATE,
        ],
    ];

    public const API_PATCH_DISABLED = [
        'swagger_definition_name' => 'Update disabled',
        'groups' => [
            Platform::GROUP_UPDATE_DISABLED
        ],
    ];

    public const API_PATCH_ENABLED = [
        'swagger_definition_name' => 'Update enabled',
        'groups' => [
            Platform::GROUP_UPDATE_ENABLED
        ],
    ];


    public const API_PATCH_NAME = [
        'swagger_definition_name' => 'Update name',
        'groups' => [
            Platform::GROUP_UPDATE_NAME,
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
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_EXACT)
     * @ApiProperty(identifier=true)
     * @Groups({Platform::GROUP_READ_DEFAULT, Platform::GROUP_CREATE})
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_PARTIAL)
     * @Groups({Platform::GROUP_READ_DEFAULT, Platform::GROUP_UPDATE, Platform::GROUP_UPDATE_NAME})
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
     * @Groups({Platform::GROUP_READ_DEFAULT, Platform::GROUP_UPDATE, Platform::GROUP_UPDATE_NAME})
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=UserGroup::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({Platform::GROUP_READ_ITEM, Platform::GROUP_READ_DEFAULT,Platform::GROUP_CREATE})
     */
    private $userGroup;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({Platform::GROUP_READ_DEFAULT, Platform::GROUP_UPDATE})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     maxMessage="Maximum number of characters is 255",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $apiHttpUrl;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({Platform::GROUP_READ_DEFAULT, Platform::GROUP_UPDATE})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     maxMessage="Maximum number of characters is 255",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $apiHttpProxy;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({Platform::GROUP_READ_DEFAULT, Platform::GROUP_UPDATE})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     maxMessage="Maximum number of characters is 255",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $apiSocketUrl;

    /**
     * @var string Platform color hexa code
     *
     * @ORM\Column(type="string", length=6, nullable=true)
     * @Groups({Platform::GROUP_READ_DEFAULT, Platform::GROUP_UPDATE})
     * @Assert\Regex("/^[0-9a-fA-F]{6}$/i")
     */
    private $color;

    /**
     * @ORM\OneToMany(targetEntity=Device::class, mappedBy="platform")
     * @ApiFilter(SearchFilter::class, properties={
     *     "devices.tags.name": SearchFilter::STRATEGY_PARTIAL
     * })
     */
    private $devices;

    /**
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="create")
     * @Groups({Platform::GROUP_READ_DEFAULT})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="update")
     * @Groups({Platform::GROUP_READ_DEFAULT})
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=TemplateModel::class, mappedBy="platform", orphanRemoval=true)
     * @Groups({Platform::GROUP_READ_ITEM})
     */
    private $templateModels;

    /**
     * @var bool Enable the platform
     *
     * @ORM\Column(type="boolean")
     * @ApiFilter(BooleanFilter::class)
     * @Groups({Platform::GROUP_READ_DEFAULT, Platform::GROUP_UPDATE, Platform::GROUP_UPDATE_ENABLED, Platform::GROUP_UPDATE_DISABLED})
     */
    private $enabled = true;

    public function __construct()
    {
        $this->devices = new ArrayCollection();
        $this->templateModels = new ArrayCollection();
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

    public function getApiHttpUrl(): ?string
    {
        return $this->apiHttpUrl;
    }

    public function setApiHttpUrl(?string $apiHttpUrl): self
    {
        $this->apiHttpUrl = $apiHttpUrl;

        return $this;
    }

    public function getApiHttpProxy(): ?string
    {
        return $this->apiHttpProxy;
    }

    public function setApiHttpProxy(?string $apiHttpProxy): self
    {
        $this->apiHttpProxy = $apiHttpProxy;

        return $this;
    }

    public function getApiSocketUrl(): ?string
    {
        return $this->apiSocketUrl;
    }

    public function setApiSocketUrl(string $apiSocketUrl): self
    {
        $this->apiSocketUrl = $apiSocketUrl;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return Collection|Device[]
     */
    public function getDevices(): Collection
    {
        return $this->devices;
    }

    public function addDevice(Device $device): self
    {
        if (!$this->devices->contains($device)) {
            $this->devices[] = $device;
            $device->setPlatform($this);
        }

        return $this;
    }

    public function removeDevice(Device $device): self
    {
        if ($this->devices->removeElement($device)) {
            // set the owning side to null (unless already changed)
            if ($device->getPlatform() === $this) {
                $device->setPlatform(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection|TemplateModel[]
     */
    public function getTemplateModels(): Collection
    {
        return $this->templateModels;
    }

    public function addTemplateModel(TemplateModel $templateModel): self
    {
        if (!$this->templateModels->contains($templateModel)) {
            $this->templateModels[] = $templateModel;
            $templateModel->setPlatform($this);
        }

        return $this;
    }

    public function removeTemplateModel(TemplateModel $templateModel): self
    {
        if ($this->templateModels->removeElement($templateModel)) {
            // set the owning side to null (unless already changed)
            if ($templateModel->getPlatform() === $this) {
                $templateModel->setPlatform(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getUserGroups(): Collection
    {
        return new ArrayCollection([$this->getUserGroup()]);
    }

    public function getUserGroup(): ?UserGroup
    {
        return $this->userGroup;
    }

    public function setUserGroup(?UserGroup $userGroup): self
    {
        $this->userGroup = $userGroup;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }


}

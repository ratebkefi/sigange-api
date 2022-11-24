<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Controller\ScreenCustomController;
use App\Dto\ScreenInput;
use App\Interfaces\TaggableInterface;
use App\Repository\ScreenRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Screen
 * @ApiFilter(PropertyFilter::class)
 * @ApiResource(
 *   input=ScreenInput::class,
 *   normalizationContext=Screen::API_READ,
 *   denormalizationContext=Screen::API_UPDATE,
 *    collectionOperations={
 *         "get"={"normalization_context"=Screen::API_READ, "security"="is_granted('ROLE_SCREEN_GET_COLLECTION')"},
 *         "post"={"denormalization_context"=Screen::API_CREATE,
 *                 "security_post_denormalize" = "is_granted('ROLE_SCREEN_POST_COLLECTION', object)"}
 *   },
 *   itemOperations={
 *         "get"={"normalization_context"=Screen::API_READ_DETAIL, "security"="is_granted('ROLE_SCREEN_GET_ITEM', object)"},
 *         "delete"={"security"="is_granted('ROLE_SCREEN_DELETE_ITEM', object)"},
 *         "put"={"denormalization_context"=Screen::API_UPDATE, "security"="is_granted('ROLE_SCREEN_PUT_ITEM', object)"},
 *         "patch"={"security"="is_granted('ROLE_SCREEN_PATCH_ITEM', object)"},
 *         "api_screens_patch_enable" = {
 *             "route_name"="api_screens_patch_enable",
 *             "controller"=ScreenCustomController::class,
 *             "method"="PATCH",
 *             "path"="/screens/{code}/enable",
 *             "denormalization_context" = Screen::API_PATCH_ENABLED,
 *             "normalization_context"= Screen::API_PATCH_ENABLED,
 *             "openapi_context" = {
 *                 "summary" = "Set the Screen as enabled true",
 *                 "description" = "Updates only the property **enabled** of a Screen and set it to `true`.",
 *             }
 *         },
 *         "api_screens_patch_disable" = {
 *             "route_name"="api_screens_patch_disable",
 *             "controller"=ScreenCustomController::class,
 *             "method"="PATCH",
 *             "path"="/screens/{code}/disable",
 *             "denormalization_context" = Screen::API_PATCH_ENABLED,
 *             "normalization_context"= Screen::API_PATCH_DISABLED,
 *             "openapi_context" = {
 *                 "summary" = "Set the Screen as enabled false",
 *                 "description" = "Updates only the property **enabled** of a Screen and set it to `false`."
 *             }
 *         },
 *         "patch_name" = {
 *             "method"="PATCH",
 *             "path"="/screens/{code}/name",
 *             "format"= "jsonld",
 *             "security"="is_granted('ROLE_SCREEN_PATCH_NAME')",
 *             "denormalization_context"= Screen::API_PATCH_NAME,
 *             "normalization_context"= Screen::API_PATCH_NAME,
 *             "openapi_context" = {
 *                 "summary" = "Updates only the name and description of a Screen",
 *                 "description" = "Updates only the  **name** and the **description** of a Video overlay. Both fields don't need to be filled at the same time"
 *             }
 *         },
 *   }
 * )
 * @ORM\Entity(repositoryClass=ScreenRepository::class)
 * @UniqueEntity("code")
 */
class Screen implements TaggableInterface
{
    use TaggableTrait;

    public const GROUP_CREATE = 'screen:create';
    public const GROUP_READ_DEFAULT = 'screen:read_default';
    public const GROUP_READ_COLLECTION = 'screen:read_list';
    public const GROUP_READ_ITEM = 'screen:read_detail';
    public const GROUP_UPDATE = 'screen:write';
    public const GROUP_UPDATE_ENABLED = 'screen:write_enabled';
    public const GROUP_UPDATE_DISABLED = 'screen:write_disabled';
    public const GROUP_UPDATE_NAME = 'screen:write_name';

    public const API_READ = [
        'swagger_definition_name' => 'Read',
        'groups' => [
            Screen::GROUP_READ_DEFAULT,
            Screen::GROUP_READ_COLLECTION,
            DeviceOutput::GROUP_READ_DEFAULT,
            Site::GROUP_READ_DEFAULT,
            Tag::GROUP_READ_DEFAULT,
            Device::GROUP_READ_DEFAULT,
        ],
    ];

    public const API_UPDATE = [
        'swagger_definition_name' => 'Update',
        'groups' => [
            Screen::GROUP_UPDATE,
            Tag::GROUP_UPDATE_RELATION
        ],
    ];

    public const API_CREATE = [
        'swagger_definition_name' => 'Create',
        'groups' => [
            Screen::GROUP_CREATE,
            Screen::GROUP_UPDATE,
            Tag::GROUP_UPDATE_RELATION
        ],
    ];

    public const API_READ_DETAIL = [
        'swagger_definition_name' => 'Detail',
        'groups' => [
            Screen::GROUP_READ_DEFAULT,
            Screen::GROUP_READ_COLLECTION,
            Screen::GROUP_READ_ITEM,
            Tag::GROUP_READ_DEFAULT,
            DeviceOutput::GROUP_READ_DEFAULT,
            Site::GROUP_READ_DEFAULT,
        ],
    ];

    public const API_PATCH_DISABLED = [
        'swagger_definition_name' => 'Update disabled',
        'groups' => [
            Screen::GROUP_UPDATE_DISABLED
        ],
    ];

    public const API_PATCH_ENABLED = [
        'swagger_definition_name' => 'Update enabled',
        'groups' => [
            Screen::GROUP_UPDATE_ENABLED
        ],
    ];

    public const API_PATCH_NAME = [
        'swagger_definition_name' => 'Update name',
        'groups' => [
            Screen::GROUP_UPDATE_NAME,
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
     * @Groups({Screen::GROUP_READ_DEFAULT, Screen::GROUP_CREATE})
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_PARTIAL)
     * @Groups({Screen::GROUP_READ_DEFAULT, Screen::GROUP_UPDATE, Screen::GROUP_UPDATE_NAME})
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
     * @Groups({Screen::GROUP_READ_DEFAULT, Screen::GROUP_UPDATE, Screen::GROUP_UPDATE_NAME})
     */
    private $description;

    /**
     * @ORM\OneToOne(targetEntity=DeviceOutput::class, inversedBy="screen", cascade={"persist", "remove"})
     * @Groups({Screen::GROUP_READ_DEFAULT, Screen::GROUP_CREATE})
     */
    private $deviceOutput;

    /**
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="create")
     * @Groups({Screen::GROUP_READ_DEFAULT})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="update")
     * @Groups({Screen::GROUP_READ_DEFAULT})
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Site::class, inversedBy="screens")
     * @ApiFilter(SearchFilter::class, properties={
     *     "site.name": SearchFilter::STRATEGY_PARTIAL
     * })
     * @Groups({Screen::GROUP_READ_DEFAULT, Screen::GROUP_CREATE})
     */
    private $site;

    /**
     * @var bool Enable the screen
     *
     * @ORM\Column(type="boolean")
     * @ApiFilter(BooleanFilter::class)
     * @Groups({Screen::GROUP_READ_DEFAULT, Screen::GROUP_UPDATE, Screen::GROUP_UPDATE_ENABLED, Screen::GROUP_UPDATE_DISABLED})
     */
    private $enabled = true;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
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

    public function getDeviceOutput(): ?DeviceOutput
    {
        return $this->deviceOutput;
    }

    public function setDeviceOutput(?DeviceOutput $deviceOutput): self
    {
        $this->deviceOutput = $deviceOutput;

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

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

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

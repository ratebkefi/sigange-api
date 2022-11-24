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
use App\Controller\DeviceCustomController;
use App\Dto\DeviceInput;
use App\Filter\OrSearchFilter;
use App\Interfaces\TaggableInterface;
use App\Interfaces\UserGroupVisibilityInterface;
use App\Interfaces\WebhookTriggeringInterface;
use App\Repository\DeviceRepository;
use DateTime;
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
 * Device
 *
 * @ApiFilter(PropertyFilter::class)
 * @ApiFilter (OrderFilter::class, properties={
 *     "code",
 *     "name",
 *     "status.name",
 *     "model.name",
 *     "userGroup.customer.name",
 *     "userGroup.name",
 *     "site.name",
 *     "site.externalId",
 *     "network.name",
 *     "platform.name",
 *     "macAddress",
 *     "serialNumber",
 *     "createdAt",
 *     "updatedAt"
 *     })
 * @ApiFilter(OrSearchFilter::class, properties={
 *             "search_global"={
 *                 "name": SearchFilter::STRATEGY_PARTIAL,
 *                 "serialNumber": SearchFilter::STRATEGY_EXACT,
 *                 "macAddress": SearchFilter::STRATEGY_EXACT,
 *                 "userGroup.name": SearchFilter::STRATEGY_PARTIAL,
 *                 "site.name": SearchFilter::STRATEGY_PARTIAL,
 *                 "network.name": SearchFilter::STRATEGY_PARTIAL,
 *                 "platform.name": SearchFilter::STRATEGY_PARTIAL,
 *                 "model.name": SearchFilter::STRATEGY_PARTIAL,
 *             },
 *             "search_network"={
 *                 "name": SearchFilter::STRATEGY_PARTIAL,
 *                 "network.name": SearchFilter::STRATEGY_PARTIAL
 *             },
 *             "search_site"={
 *                 "name": SearchFilter::STRATEGY_PARTIAL,
 *                 "site.name": SearchFilter::STRATEGY_PARTIAL
 *             }
 *           })
 * @ApiResource(
 *     input=DeviceInput::class,
 *     normalizationContext=Device::API_READ,
 *     denormalizationContext=Device::API_UPDATE,
 *     collectionOperations={
 *         "get"={"security"="is_granted('ROLE_DEVICE_GET_COLLECTION')"},
 *         "post"={"denormalization_context"=Device::API_CREATE,
 *                "security_post_denormalize" = "is_granted('ROLE_DEVICE_POST_COLLECTION', object)"}
 *     },
 *     itemOperations={
 *         "get"={"normalization_context"=Device::API_READ_DETAIL, "security"="is_granted('ROLE_DEVICE_GET_ITEM', object)"},
 *         "api_devices_get_enabled_outputs" = {
 *             "route_name"="api_devices_get_enabled_outputs",
 *             "controller"=DeviceCustomController::class,
 *             "method"="GET",
 *             "path"="/devices/{macAddress}/outputs",
 *             "security"="is_granted('ROLE_DEVICE_GET_ENABLED_OUTPUTS')",
 *             "normalization_context"= Device::API_READ_OUTPUTS,
 *             "openapi_context" = {
 *                 "summary" = "Get the DeviceOutputs of the Device if they are enabled",
 *                 "description" = "Get the DeviceOutputs of the Device and their VideoStream and VideoOVerlay only if all entities in the chain are enabled",
 *                 "parameters" = {
 *                   {
 *                     "name" = "macAddress",
 *                     "in" = "path",
 *                      "required" = true,
 *                      "type" = "string"
 *                  }
 *                }
 *             }
 *         },
 *         "delete"={"security" = "is_granted('ROLE_DEVICE_DELETE_ITEM', object)"},
 *         "put"={"denormalization_context"=Device::API_UPDATE, "security" = "is_granted('ROLE_DEVICE_PUT_ITEM', object)"},
 *         "patch"={"security" = "is_granted('ROLE_DEVICE_PATCH_ITEM', object)"},
 *         "api_devices_patch_enable" = {
 *             "route_name"="api_devices_patch_enable",
 *             "controller"=DeviceCustomController::class,
 *             "method"="PATCH",
 *             "path"="/devices/{code}/enable",
 *             "denormalization_context"= Device::API_PATCH_ENABLED,
 *             "normalization_context"= Device::API_PATCH_ENABLED,
 *             "openapi_context" = {
 *                 "summary" = "Set the Device as enabled true",
 *                 "description" = "Updates only the property **enabled** of a Device and set it to `true`."
 *             }
 *         },
 *         "api_devices_patch_disable" = {
 *             "route_name"="api_devices_patch_disable",
 *             "controller"=DeviceCustomController::class,
 *             "method"="PATCH",
 *             "path"="/devices/{code}/disable",
 *             "denormalization_context"= Device::API_PATCH_DISABLED,
 *             "normalization_context"= Device::API_PATCH_DISABLED,
 *             "openapi_context" = {
 *                 "summary" = "Set the Device as enabled false",
 *                 "description" = "Updates only the property **enabled** of a Device and set it to `false`."
 *             }
 *         },
 *         "patch_comment" = {
 *             "method"="PATCH",
 *             "path"="/devices/{code}/comment",
 *             "format"= "jsonld",
 *             "security"="is_granted('ROLE_DEVICE_PATCH_COMMENT')",
 *             "denormalization_context"= Device::API_PATCH_COMMENT,
 *             "normalization_context"= Device::API_PATCH_COMMENT,
 *             "openapi_context" = {
 *                 "summary" = "Updates only the comment of a Device",
 *                 "description" = "Updates only the public **comment** of a Device"
 *             }
 *         },
 *         "patch_internal_comment" = {
 *             "method"="PATCH",
 *             "path"="/devices/{code}/internal_comment",
 *             "format"= "jsonld",
 *             "security"="is_granted('ROLE_DEVICE_PATCH_INTERNAL_COMMENT')",
 *             "denormalization_context"= Device::API_PATCH_INTERNAL_COMMENT,
 *             "normalization_context"= Device::API_PATCH_INTERNAL_COMMENT,
 *             "openapi_context" = {
 *                 "summary" = "Updates only the internal_comment of a Device",
 *                 "description" = "Updates only the  **internalComment** of a Device"
 *             }
 *         },
 *         "patch_name" = {
 *             "method"="PATCH",
 *             "path"="/devices/{code}/name",
 *             "format"= "jsonld",
 *             "security"="is_granted('ROLE_DEVICE_PATCH_NAME')",
 *             "denormalization_context"= Device::API_PATCH_NAME,
 *             "normalization_context"= Device::API_PATCH_NAME,
 *             "openapi_context" = {
 *                 "summary" = "Updates only the name and description of a Device",
 *                 "description" = "Updates only the  **name** and the **description** of a Device. Both fields don't need to be filled at the same time"
 *             }
 *         },
 *         "patch_network" = {
 *             "method"="PATCH",
 *             "path"="/devices/{code}/network",
 *             "format"= "jsonld",
 *             "security"="is_granted('ROLE_DEVICE_PATCH_NETWORK')",
 *             "denormalization_context"= Device::API_PATCH_NETWORK,
 *             "normalization_context"= Device::API_PATCH_NETWORK,
 *             "openapi_context" = {
 *                 "summary" = "Updates only the network of a Device",
 *                 "description" = "Updates only the  **network** of a Device by sending the IRI of a network"
 *             }
 *         },
 *         "patch_platform" = {
 *             "method"="PATCH",
 *             "path"="/devices/{code}/platform",
 *             "format"= "jsonld",
 *             "security"="is_granted('ROLE_DEVICE_PATCH_PLATFORM')",
 *             "denormalization_context"= Device::API_PATCH_PLATFORM,
 *             "normalization_context"= Device::API_PATCH_PLATFORM,
 *             "openapi_context" = {
 *                 "summary" = "Updates only the platform of a Device",
 *                 "description" = "Updates only the  **platform** of a Device by sending the IRI of a platform"
 *             }
 *         },
 *         "patch_site" = {
 *             "method"="PATCH",
 *             "path"="/devices/{code}/site",
 *             "format"= "jsonld",
 *             "security"="is_granted('ROLE_DEVICE_PATCH_SITE')",
 *             "denormalization_context"= Device::API_PATCH_SITE,
 *             "normalization_context"= Device::API_PATCH_SITE,
 *             "openapi_context" = {
 *                 "summary" = "Updates only the site of a Device",
 *                 "description" = "Updates only the  **site** of a Device by sending the IRI of a site"
 *             }
 *         },
 *         "patch_status" = {
 *             "method"="PATCH",
 *             "path"="/devices/{code}/status",
 *             "format"= "jsonld",
 *             "security"="is_granted('ROLE_DEVICE_PATCH_STATUS')",
 *             "denormalization_context"= Device::API_PATCH_STATUS,
 *             "normalization_context"= Device::API_PATCH_STATUS,
 *             "openapi_context" = {
 *                 "summary" = "Updates only the status of a Device",
 *                 "description" = "Updates only the  **status** of a Device by sending the IRI of a device status"
 *             }
 *         },
 *         "api_devices_patch_remove_group" = {
 *             "route_name"="api_devices_patch_remove_group",
 *             "controller"=DeviceCustomController::class,
 *             "method"="PATCH",
 *             "path"="/devices/{code}/remove_group",
 *             "denormalization_context"= Device::API_PATCH_REMOVE_GROUP,
 *             "normalization_context"= Device::API_PATCH_REMOVE_GROUP,
 *             "openapi_context" = {
 *                 "summary" = "Removes the group of a Device",
 *                 "description" = "Removes the **group** of a Device and other operations"
 *             }
 *         },
 *     }
 * )
 * @ORM\Entity(repositoryClass=DeviceRepository::class)
 * @ORM\EntityListeners({"App\Doctrine\DeviceListener"})
 * @UniqueEntity("code")
 * @UniqueEntity("macAddress")
 * @UniqueEntity(
 *     fields={"serialNumber", "model"},
 *     message="This serialNumber is already in use with that Model."
 * )
 */
class Device implements UserGroupVisibilityInterface, TaggableInterface, WebhookTriggeringInterface
{

    use TaggableTrait;

    public const GROUP_CREATE = 'device:create';
    public const GROUP_READ_DEFAULT = 'device:read_default';
    public const GROUP_READ_COLLECTION = 'device:read_list';
    public const GROUP_READ_ITEM = 'device:read_detail';
    public const GROUP_READ_MINIMAL = 'device:read_minimal';
    public const GROUP_UPDATE = 'device:write';
    public const GROUP_UPDATE_ENABLED = 'device:write_enabled';
    public const GROUP_UPDATE_DISABLED = 'device:write_disabled';
    public const GROUP_REMOVE_GROUP = 'device:remove_group';
    public const GROUP_UPDATE_NAME = 'device:write_name';
    public const GROUP_UPDATE_COMMENT = 'device:write_comment';
    public const GROUP_UPDATE_INTERNAL_COMMENT = 'device:write_internal_comment';
    public const GROUP_UPDATE_NETWORK = 'device:write_network';
    public const GROUP_UPDATE_STATUS = 'device:write_status';
    public const GROUP_UPDATE_SITE = 'device:write_site';
    public const GROUP_UPDATE_PLATFORM = 'device:write_platform';
    public const API_ENABLED_OUTPUTS = 'device:outputs:enabled';

    public const API_CREATE = [
        'swagger_definition_name' => 'Create',
        'groups' => [
            Device::GROUP_CREATE,
            Device::GROUP_UPDATE,
            Tag::GROUP_UPDATE_RELATION
        ],
    ];
    public const API_READ = [
        'swagger_definition_name' => 'Read',
        'groups' => [
            Device::GROUP_READ_DEFAULT,
            Device::GROUP_READ_COLLECTION,
            DeviceDiagnostic::GROUP_READ_DEFAULT,
            DeviceModel::GROUP_READ_DEFAULT,
            DeviceOutput::GROUP_READ_DEFAULT,
            DeviceStatus::GROUP_READ_DEFAULT,
            Network::GROUP_READ_DEFAULT,
            Platform::GROUP_READ_DEFAULT,
            Site::GROUP_READ_DEFAULT,
            UserGroup::GROUP_READ_DEFAULT,
            Tag::GROUP_READ_DEFAULT,
            Customer::GROUP_READ_DEFAULT
        ],
    ];
    public const API_READ_DETAIL = [
        'swagger_definition_name' => 'Detail',
        'groups' => [
            Device::GROUP_READ_DEFAULT,
            Device::GROUP_READ_COLLECTION,
            Device::GROUP_READ_ITEM,
            DeviceDiagnostic::GROUP_READ_DEFAULT,
            DeviceModel::GROUP_READ_DEFAULT,
            DeviceModel::GROUP_READ_COLLECTION,
            DeviceModelOutput::GROUP_READ_DEFAULT,
            DeviceOutput::GROUP_READ_DEFAULT,
            DeviceStatus::GROUP_READ_DEFAULT,
            Network::GROUP_READ_DEFAULT,
            Platform::GROUP_READ_DEFAULT,
            Site::GROUP_READ_DEFAULT,
            Tag::GROUP_READ_DEFAULT,
            TagGroup::GROUP_READ_DEFAULT,
            UserGroup::GROUP_READ_DEFAULT,
            VideoStream::GROUP_READ_DEFAULT,
            VideoOverlay::GROUP_READ_DEFAULT,
            Customer::GROUP_READ_DEFAULT,
        ],
    ];

    public const API_READ_OUTPUTS = [
        'swagger_definition_name' => 'Read Outputs',
        'groups' => [
            Device::API_ENABLED_OUTPUTS,
            Device::GROUP_READ_MINIMAL,
            DeviceOutput::GROUP_READ_DEFAULT,
            DeviceModelOutput::GROUP_READ_MINIMAL
        ],
    ];

    public const API_UPDATE = [
        'swagger_definition_name' => 'Update',
        'groups' => [
            Device::GROUP_UPDATE,
            Tag::GROUP_UPDATE_RELATION
        ],
    ];


    public const API_PATCH_NAME = [
        'swagger_definition_name' => 'Update name',
        'groups' => [
            Device::GROUP_UPDATE_NAME,
        ],
    ];

    public const API_PATCH_COMMENT = [
        'swagger_definition_name' => 'Update comment',
        'groups' => [
            Device::GROUP_UPDATE_COMMENT,
        ],
    ];

    public const API_PATCH_INTERNAL_COMMENT = [
        'swagger_definition_name' => 'Update internalComment',
        'groups' => [
            Device::GROUP_UPDATE_INTERNAL_COMMENT,
        ],
    ];

    public const API_PATCH_DISABLED = [
        'swagger_definition_name' => 'Update disabled',
        'groups' => [
            Device::GROUP_UPDATE_DISABLED
        ],
    ];

    public const API_PATCH_ENABLED = [
        'swagger_definition_name' => 'Update enabled',
        'groups' => [
            Device::GROUP_UPDATE_ENABLED
        ],
    ];

    public const API_PATCH_REMOVE_GROUP = [
        'swagger_definition_name' => 'Update group',
        'groups' => [
            Device::GROUP_REMOVE_GROUP
        ],
    ];

    public const API_PATCH_NETWORK = [
        'swagger_definition_name' => 'Update network',
        'groups' => [
            Device::GROUP_UPDATE_NETWORK,
            Network::GROUP_READ_DEFAULT
        ],
    ];

    public const API_PATCH_STATUS = [
        'swagger_definition_name' => 'Update status',
        'groups' => [
            Device::GROUP_UPDATE_STATUS,
            DeviceStatus::GROUP_READ_DEFAULT,
        ],
    ];

    public const API_PATCH_SITE = [
        'swagger_definition_name' => 'Update site',
        'groups' => [
            Device::GROUP_UPDATE_SITE,
            Site::GROUP_READ_DEFAULT
        ],
    ];

    public const API_PATCH_PLATFORM = [
        'swagger_definition_name' => 'Update platform',
        'groups' => [
            Device::GROUP_UPDATE_PLATFORM,
            Platform::GROUP_READ_DEFAULT
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
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_EXACT)
     * @Groups({Device::GROUP_READ_DEFAULT, Device::GROUP_CREATE})
     */
    private $code;

    /**
     * @var string Name of the device
     *
     * @ORM\Column(type="string", length=255)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_PARTIAL)
     * @Groups({Device::GROUP_READ_DEFAULT, Device::GROUP_UPDATE, Device::GROUP_UPDATE_NAME, Device::GROUP_READ_MINIMAL})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     maxMessage="Maximum number of characters is 255",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $name;

    /**
     * @var string Description of the device
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_PARTIAL)
     * @ORM\Column(type="text", nullable=true)
     * @Groups({Device::GROUP_READ_DEFAULT, Device::GROUP_UPDATE, Device::GROUP_UPDATE_NAME})
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=UserGroup::class)
     * @ORM\JoinColumn(nullable=true)
     * @Groups({Device::GROUP_READ_DEFAULT,Device::GROUP_UPDATE, Device::GROUP_REMOVE_GROUP})
     * @ApiFilter(SearchFilter::class, properties={
     *     "userGroup.name": SearchFilter::STRATEGY_PARTIAL,
     *     "userGroup.customer.name": SearchFilter::STRATEGY_PARTIAL,
     * })
     */
    private $userGroup;

    /**
     * @var bool Enable the device
     *
     * @ORM\Column(type="boolean")
     * @ApiFilter(BooleanFilter::class)
     * @Groups({Device::GROUP_READ_DEFAULT, Device::GROUP_UPDATE, Device::GROUP_UPDATE_ENABLED, Device::GROUP_UPDATE_ENABLED, Device::GROUP_UPDATE_DISABLED,Device::GROUP_READ_ITEM, Device::GROUP_READ_MINIMAL})
     */
    private $enabled = true;

    /**
     * @var DeviceModel Model of the device (not updatable after creation)
     *
     * @ORM\ManyToOne(targetEntity=DeviceModel::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({Device::GROUP_READ_DEFAULT, Device::GROUP_CREATE})
     */
    private $model;

    /**
     * @var Site Location of the device
     * @ORM\ManyToOne(targetEntity=Site::class, inversedBy="devices")
     * @ApiFilter(SearchFilter::class, properties={
     *     "site.name": SearchFilter::STRATEGY_PARTIAL,
     *     "site.externalId": SearchFilter::STRATEGY_EXACT
     * })
     * @Groups({Device::GROUP_READ_DEFAULT, Device::GROUP_UPDATE, Device::GROUP_UPDATE_SITE, Device::GROUP_REMOVE_GROUP})
     */
    private $site;

    /**
     * @var Network Network
     * @ORM\ManyToOne(targetEntity=Network::class, inversedBy="devices")
     * @ApiFilter(SearchFilter::class, properties={
     *     "network.name": "partial"
     * })
     * @Groups({Device::GROUP_READ_COLLECTION, Device::GROUP_UPDATE, Device::GROUP_UPDATE_NETWORK})
     */
    private $network;

    /**
     * @var Platform Platform
     *
     * @ORM\ManyToOne(targetEntity=Platform::class, inversedBy="devices")
     * @ApiFilter(SearchFilter::class, properties={
     *     "platform.name": "partial"
     * })
     * @Groups({Device::GROUP_READ_COLLECTION, Device::GROUP_UPDATE, Device::GROUP_UPDATE_PLATFORM})
     */
    private $platform;

    /**
     * @var string Comment about the device
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({Device::GROUP_READ_ITEM, Device::GROUP_UPDATE, Device::GROUP_UPDATE_COMMENT})
     */
    private $comment;

    /**
     * @var string Comment about the device (only for SUPER_ADMIN)
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({Device::GROUP_READ_ITEM, Device::GROUP_UPDATE, Device::GROUP_UPDATE_INTERNAL_COMMENT})
     */
    private $internalComment;

    /**
     * @var string Serial number of the device
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_EXACT)
     * @Groups({Device::GROUP_READ_COLLECTION, Device::GROUP_UPDATE})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     maxMessage="Maximum number of characters is 255",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $serialNumber;

    /**
     * @var string MAC address of the device
     *
     * @ORM\Column(type="string", length=17)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_EXACT)
     * @Groups({Device::GROUP_READ_DEFAULT, Device::GROUP_UPDATE})
     * @Assert\Regex("/^([A-F0-9]{2}:){5}[a-fA-F0-9]{2}$/i")
     * @Assert\Length(
     *     min=2,
     *     max=17,
     *     maxMessage="Maximum number of characters is 17",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $macAddress;

    /**
     * @var string OS version
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups({Device::GROUP_READ_ITEM, Device::GROUP_UPDATE})
     * @Assert\Length(
     *     min=1,
     *     max=10,
     *     maxMessage="Maximum number of characters is 10",
     *     minMessage="Minimum number of characters is 1"
     * )
     */
    private $osVersion;

    /**
     * @var string OS version wanted (the device will self-update to this version)
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups({Device::GROUP_READ_ITEM, Device::GROUP_UPDATE})
     * @Assert\Length(
     *     min=1,
     *     max=10,
     *     maxMessage="Maximum number of characters is 10",
     *     minMessage="Minimum number of characters is 1"
     * )
     */
    private $wantedOsVersion;

    /**
     * @var string Software version
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups({Device::GROUP_READ_ITEM, Device::GROUP_UPDATE})
     * @Assert\Length(
     *     min=1,
     *     max=10,
     *     maxMessage="Maximum number of characters is 10",
     *     minMessage="Minimum number of characters is 1"
     * )
     */
    private $softwareVersion;

    /**
     * @var string Software version wanted (the device will self-update to this version)
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups({Device::GROUP_READ_ITEM, Device::GROUP_UPDATE})
     * @Assert\Length(
     *     min=1,
     *     max=10,
     *     maxMessage="Maximum number of characters is 10",
     *     minMessage="Minimum number of characters is 1"
     * )
     */
    private $wantedSoftwareVersion;

    /**
     * @var bool SSH access enabled
     *
     * @ORM\Column(type="boolean")
     * @ApiFilter(BooleanFilter::class)
     * @Groups({Device::GROUP_READ_COLLECTION, Device::GROUP_UPDATE})
     */
    private $isSshEnabled;

    /**
     * @var bool VPN access enabled
     *
     * @ORM\Column(type="boolean")
     * @ApiFilter(BooleanFilter::class)
     * @Groups({Device::GROUP_READ_COLLECTION, Device::GROUP_UPDATE})
     */
    private $isVpnEnabled;

    /**
     * @var DeviceDiagnostic Contains frequently updated data (ping, last access...)
     *
     * @ORM\OneToOne(targetEntity=DeviceDiagnostic::class, mappedBy="device", cascade={"persist", "remove"})
     * @Groups({Device::GROUP_READ_ITEM, Device::GROUP_READ_DEFAULT})
     */
    private $diagnostic;

    /**
     * @var DeviceStatus Status of the device
     *
     * @ORM\ManyToOne(targetEntity=DeviceStatus::class)
     * @ORM\JoinColumn(nullable=false)
     * @ApiFilter(SearchFilter::class, properties={
     *     "status.name": "partial"
     * })
     * @Groups({Device::GROUP_READ_COLLECTION, Device::GROUP_UPDATE, Device::GROUP_UPDATE_STATUS})
     */
    private $status;

    /**
     * @var Collection|DeviceOutput[] Device video outputs
     * @ORM\OneToMany(targetEntity=DeviceOutput::class, mappedBy="device", orphanRemoval=true, cascade={"persist"})
     * @Groups({Device::GROUP_READ_COLLECTION, Device::GROUP_READ_DEFAULT,Device::GROUP_CREATE})
     */
    private $outputs;

    /**
     * @var DateTime Creation date
     *
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Groups({Device::GROUP_READ_DEFAULT})
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @var DateTime Last update date
     *
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Groups({Device::GROUP_READ_DEFAULT})
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    public function __construct()
    {
        $this->outputs = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->diagnostic = (new DeviceDiagnostic())
            ->setCode(Uuid::v4())
            ->setLastHttpConnectionAt(new \DateTime())
            ->setDevice($this);
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

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

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

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }

    public function getNetwork(): ?Network
    {
        return $this->network;
    }

    public function setNetwork(?Network $network): self
    {
        $this->network = $network;

        return $this;
    }

    public function getPlatform(): ?Platform
    {
        return $this->platform;
    }

    public function setPlatform(?Platform $platform): self
    {
        $this->platform = $platform;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getInternalComment(): ?string
    {
        return $this->internalComment;
    }

    public function setInternalComment(?string $internalComment): self
    {
        $this->internalComment = $internalComment;

        return $this;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(string $serialNumber): self
    {
        $this->serialNumber = trim($serialNumber);

        return $this;
    }

    public function getMacAddress(): ?string
    {
        return $this->macAddress;
    }

    public function setMacAddress(string $macAddress): self
    {
        $this->macAddress = strtoupper(trim($macAddress));

        return $this;
    }

    public function getOsVersion(): ?string
    {
        return $this->osVersion;
    }

    public function setOsVersion(?string $osVersion): self
    {
        $this->osVersion = trim($osVersion);

        return $this;
    }

    public function getWantedOsVersion(): ?string
    {
        return $this->wantedOsVersion;
    }

    public function setWantedOsVersion(?string $wantedOsVersion): self
    {
        $this->wantedOsVersion = trim($wantedOsVersion);

        return $this;
    }

    public function getSoftwareVersion(): ?string
    {
        return $this->softwareVersion;
    }

    public function setSoftwareVersion(?string $softwareVersion): self
    {
        $this->softwareVersion = trim($softwareVersion);

        return $this;
    }

    public function getWantedSoftwareVersion(): ?string
    {
        return $this->wantedSoftwareVersion;
    }

    public function setWantedSoftwareVersion(?string $wantedSoftwareVersion): self
    {
        $this->wantedSoftwareVersion = trim($wantedSoftwareVersion);

        return $this;
    }

    public function getIsSshEnabled(): ?bool
    {
        return $this->isSshEnabled;
    }

    public function setIsSshEnabled(bool $isSshEnabled): self
    {
        $this->isSshEnabled = $isSshEnabled;

        return $this;
    }

    public function getIsVpnEnabled(): ?bool
    {
        return $this->isVpnEnabled;
    }

    public function setIsVpnEnabled(bool $isVpnEnabled): self
    {
        $this->isVpnEnabled = $isVpnEnabled;

        return $this;
    }

    public function getDiagnostic(): ?DeviceDiagnostic
    {
        return $this->diagnostic;
    }

    public function setDiagnostic(DeviceDiagnostic $diagnostic): self
    {
        $this->diagnostic = $diagnostic;

        // set the owning side of the relation if necessary
        if ($diagnostic->getDevice() !== $this) {
            $diagnostic->setDevice($this);
        }

        return $this;
    }

    public function getStatus(): ?DeviceStatus
    {
        return $this->status;
    }

    public function setStatus(?DeviceStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|DeviceOutput[]
     */
    public function getOutputs(): Collection
    {
        return $this->outputs;
    }

    /**
     * Get only the enabled outputs data.
     * The following entities have to be enabled: Device, DeviceOutput, VideoOverlay, VideoStream.
     * If at least one of the entities in the chain is disabled, don't return the DeviceOutput.
     * @return Collection|DeviceOutput[]
     * @Groups(Device::API_ENABLED_OUTPUTS)
     */
    public function getEnabledOutputs(): Collection
    {
        $enabledOutputs = new ArrayCollection();

        foreach ($this->outputs as $output) {
            if ($output->isFullyEnabled() === true) {
                $enabledOutputs->add($output);
            }
        }
        return $enabledOutputs;
    }


    public function addOutput(DeviceOutput $output): self
    {
        if (!$this->outputs->contains($output)) {
            $this->outputs[] = $output;
            $output->setDevice($this);
        }

        return $this;
    }

    public function removeOutput(DeviceOutput $output): self
    {
        if ($this->outputs->removeElement($output)) {
            // set the owning side to null (unless already changed)
            if ($output->getDevice() === $this) {
                $output->setDevice(null);
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

}

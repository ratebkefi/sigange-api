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
use App\Controller\NetworkCustomController;
use App\Dto\NetworkInput;
use App\Interfaces\TaggableInterface;
use App\Interfaces\UserGroupVisibilityInterface;
use App\Repository\NetworkRepository;
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
 * Network
 * @ApiFilter(PropertyFilter::class)
 * @ApiFilter (OrderFilter::class, properties={
 *     "code",
 *     "name",
 *     "userGroup.name",
 *     "publicIpV4",
 *     "publicIpV6",
 *     "gatewayIpV4",
 *     "gatewayIpV6",
 *     "ssh",
 *     "createdAt",
 *     "updatedAt"
 *     })
 * @ApiResource(
 *     input=NetworkInput::class,
 *     normalizationContext=Network::API_READ,
 *     denormalizationContext=Network::API_UPDATE,
 *     collectionOperations={
 *         "get"={"security"="is_granted('ROLE_NETWORK_GET_COLLECTION')"},
 *         "post"={"denormalization_context"=Network::API_CREATE,
 *                 "security_post_denormalize" = "is_granted('ROLE_NETWORK_POST_COLLECTION', object)"}
 *     },
 *     itemOperations={
 *         "get"={"normalization_context"=Network::API_READ_DETAIL,
 *                  "security"="is_granted('ROLE_NETWORK_GET_ITEM', object)"},
 *         "delete"={"security"="is_granted('ROLE_NETWORK_DELETE_ITEM', object)"},
 *         "put"={"denormalization_context"=Network::API_UPDATE,  "security"="is_granted('ROLE_NETWORK_PUT_ITEM', object)"},
 *         "patch"={"security"="is_granted('ROLE_NETWORK_PATCH_ITEM', object)"},
 *         "api_networks_patch_enable" = {
 *             "route_name"="api_networks_patch_enable",
 *             "controller"=NetworkCustomController::class,
 *             "method"="PATCH",
 *             "path"="/networks/{code}/enable",
 *             "normalization_context"= Network::API_PATCH_ENABLED,
 *             "openapi_context" = {
 *                 "summary" = "Set the Network as enabled true",
 *                 "description" = "Updates only the property **enabled** of a Network and set it to `true`."
 *             }
 *         },
 *         "api_networks_patch_disable" = {
 *             "route_name"="api_networks_patch_disable",
 *             "controller"=NetworkCustomController::class,
 *             "method"="PATCH",
 *             "path"="/networks/{code}/disable",
 *             "normalization_context"= Network::API_PATCH_DISABLED,
 *             "openapi_context" = {
 *                 "summary" = "Set the Network as enabled false",
 *                 "description" = "Updates only the property **enabled** of a Network and set it to `false`."
 *             }
 *         },
 *        "patch_name" = {
 *             "method"="PATCH",
 *             "path"="/networks/{code}/name",
 *             "format"= "jsonld",
 *             "security"="is_granted('ROLE_NETWORK_PATCH_NAME')",
 *             "denormalization_context"= Network::API_PATCH_NAME,
 *             "normalization_context"= Network::API_PATCH_NAME,
 *             "openapi_context" = {
 *                 "summary" = "Updates only the name and description of a Network",
 *                 "description" = "Updates only the  **name** and the **description** of a Network. Both fields don't need to be filled at the same time"
 *             }
 *         },
 *     }
 * )
 * @ORM\Entity(repositoryClass=NetworkRepository::class)
 * @UniqueEntity("code")
 */
class Network implements UserGroupVisibilityInterface, TaggableInterface
{
    use TaggableTrait;

    public const GROUP_CREATE = 'network:create';
    public const GROUP_READ_DEFAULT = 'network:read_default';
    public const GROUP_READ_COLLECTION = 'network:read_list';
    public const GROUP_READ_ITEM = 'network:read_detail';
    public const GROUP_UPDATE = 'network:write';
    public const GROUP_UPDATE_ENABLED = 'network:write_enabled';
    public const GROUP_UPDATE_DISABLED = 'network:write_disabled';
    public const GROUP_UPDATE_NAME = 'network:write_name';

    public const API_CREATE = [
        'swagger_definition_name' => 'Create',
        'groups' => [
            Network::GROUP_CREATE,
            Network::GROUP_UPDATE,
            Tag::GROUP_UPDATE_RELATION
        ],
    ];
    public const API_READ = [
        'swagger_definition_name' => 'Read',
        'groups' => [
            Network::GROUP_READ_DEFAULT,
            Network::GROUP_READ_COLLECTION,
            UserGroup::GROUP_READ_DEFAULT,
            Site::GROUP_READ_DEFAULT,
            Tag::GROUP_READ_DEFAULT,
        ],
    ];
    public const API_READ_DETAIL = [
        'swagger_definition_name' => 'Details',
        'groups' => [
            Network::GROUP_READ_DEFAULT,
            Network::GROUP_READ_COLLECTION,
            Network::GROUP_READ_ITEM,
            UserGroup::GROUP_READ_DEFAULT,
            Site::GROUP_READ_DEFAULT,
            Tag::GROUP_READ_DEFAULT,
        ],
    ];
    public const API_UPDATE = [
        'swagger_definition_name' => 'Update',
        'groups' => [
            Network::GROUP_UPDATE,
            Tag::GROUP_UPDATE_RELATION
        ],
    ];

    public const API_PATCH_DISABLED = [
        'swagger_definition_name' => 'Update disabled',
        'groups' => [
            Network::GROUP_UPDATE_DISABLED
        ],
    ];

    public const API_PATCH_ENABLED = [
        'swagger_definition_name' => 'Update enabled',
        'groups' => [
            Network::GROUP_UPDATE_ENABLED
        ],
    ];


    public const API_PATCH_NAME = [
        'swagger_definition_name' => 'Update name',
        'groups' => [
            Network::GROUP_UPDATE_NAME,
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
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_CREATE})
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_PARTIAL)
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_UPDATE, Network::GROUP_UPDATE_NAME})
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
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_UPDATE, Network::GROUP_UPDATE_NAME})
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=UserGroup::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_UPDATE})
     */
    private $userGroup;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_UPDATE})
     * @Assert\Length(
     *     min=7,
     *     max=15,
     *     maxMessage="Maximum number of characters is 15",
     *     minMessage="Minimum number of characters is 7"
     * )
     */
    private $publicIpV4;

    /**
     * @ORM\Column(type="string", length=46, nullable=true)
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_UPDATE})
     * @Assert\Length(
     *     min=12,
     *     max=46,
     *     minMessage="Minimum number of characters is 12",
     *     maxMessage="Maximum number of characters is 46"
     * )
     */
    private $publicIpV6;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_UPDATE})
     * @Assert\Length(
     *     min=7,
     *     max=15,
     *     maxMessage="Maximum number of characters is 15",
     *     minMessage="Minimum number of characters is 7"
     * )
     */
    private $gatewayIpV4;

    /**
     * @ORM\Column(type="string", length=46, nullable=true)
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_UPDATE})
     * @Assert\Length(
     *     min=12,
     *     max=46,
     *     minMessage="Minimum number of characters is 12",
     *     maxMessage="Maximum number of characters is 46"
     * )
     */
    private $gatewayIpV6;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_UPDATE})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     maxMessage="Maximum number of characters is 255",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $ssh;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_UPDATE})
     */
    private $comment;

    /**
     * @ORM\OneToMany(targetEntity=Device::class, mappedBy="network")
     */
    private $devices;

    /**
     * FIXME relation saved only on owning side ( persist network), event with cascade persist
     * @ORM\ManyToMany(targetEntity=Site::class, inversedBy="networks")
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_UPDATE})
     */
    private $sites;

    /**
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="create")
     * @Groups({Network::GROUP_READ_DEFAULT})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="update")
     * @Groups({Network::GROUP_READ_DEFAULT})
     */
    private $updatedAt;

    /**
     * @var bool Enable the network
     *
     * @ORM\Column(type="boolean")
     * @ApiFilter(BooleanFilter::class)
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_UPDATE, Network::GROUP_UPDATE_ENABLED, Network::GROUP_UPDATE_DISABLED})
     */
    private $enabled = true;

    public function __construct()
    {
        $this->devices = new ArrayCollection();
        $this->sites = new ArrayCollection();
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

    public function getPublicIpV4(): ?string
    {
        return $this->publicIpV4;
    }

    public function setPublicIpV4(?string $publicIpV4): self
    {
        $this->publicIpV4 = trim($publicIpV4);

        return $this;
    }

    public function getPublicIpV6(): ?string
    {
        return $this->publicIpV6;
    }

    public function setPublicIpV6(?string $publicIpV6): self
    {
        $this->publicIpV6 = $publicIpV6;

        return $this;
    }

    public function getGatewayIpV4(): ?string
    {
        return $this->gatewayIpV4;
    }

    public function setGatewayIpV4(?string $gatewayIpV4): self
    {
        $this->gatewayIpV4 = trim($gatewayIpV4);

        return $this;
    }

    public function getGatewayIpV6(): ?string
    {
        return $this->gatewayIpV6;
    }

    public function setGatewayIpV6(?string $gatewayIpV6): self
    {
        $this->gatewayIpV6 = trim($gatewayIpV6);

        return $this;
    }

    public function getSsh(): ?string
    {
        return $this->ssh;
    }

    public function setSsh(?string $ssh): self
    {
        $this->ssh = $ssh;

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
            $device->setNetwork($this);
        }

        return $this;
    }

    public function removeDevice(Device $device): self
    {
        if ($this->devices->removeElement($device)) {
            // set the owning side to null (unless already changed)
            if ($device->getNetwork() === $this) {
                $device->setNetwork(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Site[]
     */
    public function getSites(): Collection
    {
        return $this->sites;
    }

    public function addSite(Site $site): self
    {
        if (!$this->sites->contains($site)) {
            $this->sites[] = $site;
        }

        return $this;
    }

    public function removeSite(Site $site): self
    {
        $this->sites->removeElement($site);

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

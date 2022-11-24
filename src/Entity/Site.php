<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Controller\SiteCustomController;
use App\Dto\SiteInput;
use App\Entity\Embeddable\Address;
use App\Entity\Embeddable\Contact;
use App\Filter\OrSearchFilter;
use App\Interfaces\TaggableInterface;
use App\Interfaces\UserGroupVisibilityInterface;
use App\Interfaces\WatchableInterface;
use App\Interfaces\WebhookTriggeringInterface;
use App\Repository\SiteRepository;
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
 * Site
 *
 * @ApiFilter(PropertyFilter::class)
 * @ApiFilter(OrSearchFilter::class, properties={
 *             "search_default"={
 *                 "name": SearchFilter::STRATEGY_PARTIAL,
 *                 "externalId": SearchFilter::STRATEGY_EXACT
 *             },
 *             "search_global"={
 *                 "name": SearchFilter::STRATEGY_PARTIAL,
 *                 "externalId": SearchFilter::STRATEGY_EXACT,
 *                 "userGroup.name": SearchFilter::STRATEGY_PARTIAL,
 *                 "templateModel.name": SearchFilter::STRATEGY_PARTIAL,
 *             }
 *           })
 * @ApiFilter (OrderFilter::class, properties={
 *     "code",
 *     "name",
 *     "externalId",
 *     "userGroup.name",
 *     "templateModel.name",
 *     "createdAt",
 *     "updatedAt"
 *     })
 * @ApiResource(
 *   input=SiteInput::class,
 *     normalizationContext=Site::API_READ,
 *     denormalizationContext=Site::API_UPDATE,
 *     collectionOperations={
 *         "get"={"security"="is_granted('ROLE_SITE_GET_COLLECTION')"},
 *         "post"={"denormalization_context"=Site::API_CREATE,
 *                 "security_post_denormalize" = "is_granted('ROLE_SITE_POST_COLLECTION', object)"}
 *     },
 *     itemOperations={
 *         "get"={"normalization_context"=Site::API_READ_DETAIL, "security"="is_granted('ROLE_SITE_GET_ITEM', object)"},
 *         "delete"={"security"="is_granted('ROLE_SITE_DELETE_ITEM', object)"},
 *         "put"={"denormalization_context"=Site::API_UPDATE, "security"="is_granted('ROLE_SITE_PUT_ITEM', object)"},
 *         "patch"={"security"="is_granted('ROLE_SITE_PATCH_ITEM', object)"},
 *         "api_sites_patch_enable" = {
 *             "route_name"="api_sites_patch_enable",
 *             "controller"=SiteCustomController::class,
 *             "method"="PATCH",
 *             "path"="/sites/{code}/enable",
 *             "normalization_context"= Site::API_PATCH_ENABLED,
 *             "denormalization_context"= Site::API_PATCH_ENABLED,
 *             "openapi_context" = {
 *                 "summary" = "Enable the Site",
 *                 "description" = "Set the Site as `enabled: true`"
 *             }
 *         },
 *         "api_sites_patch_disable" = {
 *             "route_name"="api_sites_patch_disable",
 *             "controller"=SiteCustomController::class,
 *             "method"="PATCH",
 *             "path"="/sites/{code}/disable",
 *             "denormalization_context"= Site::API_PATCH_DISABLED,
 *             "normalization_context"= Site::API_PATCH_DISABLED,
 *             "openapi_context" = {
 *                 "summary" = "Disable the Site",
 *                 "description" = "Set the Site as `enabled: false`"
 *             }
 *         },
 *         "patch_name" = {
 *             "method"="PATCH",
 *             "path"="/sites/{code}/name",
 *             "format"= "jsonld",
 *             "security"="is_granted('ROLE_SITE_PATCH_NAME')",
 *             "denormalization_context"= Site::API_PATCH_NAME,
 *             "normalization_context"= Site::API_PATCH_NAME,
 *             "openapi_context" = {
 *                 "summary" = "Updates the name and description",
 *                 "description" = "Updates only the **name** and **description** of the Site. Both fields don't need to be filled at the same time"
 *             }
 *         },
 *     }
 * )
 * @ORM\Entity(repositoryClass=SiteRepository::class)
 * @UniqueEntity("code")
 */
class Site implements UserGroupVisibilityInterface, TaggableInterface, WatchableInterface, WebhookTriggeringInterface
{

    use TaggableTrait;

    public const GROUP_CREATE = 'site:create';
    public const GROUP_READ_DEFAULT = 'site:read_default';
    public const GROUP_READ_COLLECTION = 'site:read_list';
    public const GROUP_READ_ITEM = 'site:read_detail';
    public const GROUP_UPDATE = 'site:write';
    public const GROUP_UPDATE_NAME = 'site:write_name';
    public const GROUP_UPDATE_ENABLED = 'site:write_enabled';
    public const GROUP_UPDATE_DISABLED = 'site:write_disabled';


    public const API_CREATE = [
        'swagger_definition_name' => 'Create',
        'groups' => [
            Site::GROUP_CREATE,
            Site::GROUP_UPDATE,
            Contact::GROUP_WRITE,
            Address::GROUP_WRITE,
            Tag::GROUP_UPDATE_RELATION
        ],
    ];
    public const API_READ = [
        'swagger_definition_name' => 'Read',
        'groups' => [
            Site::GROUP_READ_DEFAULT,
            Site::GROUP_READ_COLLECTION,
            UserGroup::GROUP_READ_DEFAULT,
            Network::GROUP_READ_DEFAULT,
            Tag::GROUP_READ_DEFAULT,
            Contact::GROUP_READ,
            Address::GROUP_READ
        ],
    ];
    const API_READ_DETAIL = [
        'swagger_definition_name' => 'Detail',
        'groups' => [
            Site::GROUP_READ_DEFAULT,
            Site::GROUP_READ_COLLECTION,
            Site::GROUP_READ_ITEM,
            UserGroup::GROUP_READ_DEFAULT,
            Device::GROUP_READ_DEFAULT,
            DeviceOutput::GROUP_READ_DEFAULT,
            Network::GROUP_READ_DEFAULT,
            Tag::GROUP_READ_DEFAULT,
            TemplateModel::GROUP_READ_DEFAULT,
            TemplateModelOutput::GROUP_READ_DEFAULT,
            VideoStream::GROUP_READ_DEFAULT,
            VideoOverlay::GROUP_READ_DEFAULT,
            Contact::GROUP_READ,
            Address::GROUP_READ
        ],
    ];
    const API_UPDATE = [
        'swagger_definition_name' => 'Update',
        'groups' => [
            Site::GROUP_UPDATE,
            Contact::GROUP_WRITE,
            Address::GROUP_WRITE,
            Tag::GROUP_UPDATE_RELATION,
        ],
    ];

    const API_PATCH_NAME = [
        'swagger_definition_name' => 'Update name',
        'groups' => [
            Site::GROUP_UPDATE_NAME,
        ],
    ];
    const API_PATCH_DISABLED = [
        'swagger_definition_name' => 'Update disabled',
        'groups' => [
            Site::GROUP_UPDATE_DISABLED
        ],
    ];

    const API_PATCH_ENABLED = [
        'swagger_definition_name' => 'Update enabled',
        'groups' => [
            Site::GROUP_UPDATE_ENABLED
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
     * @Groups({Site::GROUP_READ_DEFAULT, Site::GROUP_CREATE})
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_PARTIAL)
     * @Groups({Site::GROUP_READ_DEFAULT, Site::GROUP_UPDATE, Site::GROUP_UPDATE_NAME})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     maxMessage="Maximum number of characters is 255",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_PARTIAL)
     * @Groups({Site::GROUP_READ_DEFAULT, Site::GROUP_UPDATE})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     maxMessage="Maximum number of characters is 255",
     *     minMessage="Minimum number of characters is 2"
     *)
     * @Assert\Regex(
     *     pattern="/^[a-zA-Z0-9\-\_]+$/",
     *     message="Your property should match /^[a-zA-Z0-9\-\_]+$/"
     * )
     */
    private $externalId;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({Site::GROUP_READ_DEFAULT, Site::GROUP_UPDATE, Site::GROUP_UPDATE_NAME})
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=UserGroup::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({Site::GROUP_READ_DEFAULT, Site::GROUP_UPDATE})
     */
    private $userGroup;

    /**
     * @ORM\Embedded(class=Embeddable\Address::class, columnPrefix="address_")
     * @Groups({Site::GROUP_READ_DEFAULT, Site::GROUP_UPDATE, Site::GROUP_CREATE})
     * @ApiProperty(
     *     attributes={
     *         "openapi_context"={"type": "object",
     *             "properties": {
     *                 "country": {"type": "string"},
     *                 "city": {"type": "string"},
     *                 "zipCode": {"type": "string"},
     *                 "street": {"type": "string"},
     *              }
     *         }
     *     }
     * )
     */
    private Address $address;

    /**
     * @ORM\Embedded(class=Embeddable\Contact::class, columnPrefix="contact_")
     * @Groups({Site::GROUP_READ_DEFAULT, Site::GROUP_UPDATE, Site::GROUP_CREATE})
     * @ApiProperty(
     *     attributes={
     *         "openapi_context"={"type": "object",
     *             "properties": {
     *                 "firstName": {"type": "string"},
     *                 "lastName": {"type": "string"},
     *                 "gender": {"type": "string"},
     *                 "email": {"type": "string"},
     *                 "phone": {"type": "string"},
     *              }
     *         }
     *     }
     * )
     */
    private Contact $contact;

    /**
     * @ORM\OneToMany(targetEntity=Device::class, mappedBy="site")
     * @Groups({Site::GROUP_READ_ITEM})
     */
    private $devices;

    /**
     * @ORM\OneToMany(targetEntity=Screen::class, mappedBy="site")
     * @Groups({Site::GROUP_READ_DEFAULT})
     */
    private $screens;

    /**
     * FIXME relation saved only on owning side ( persist network), event with cascade persist
     * @ORM\ManyToMany(targetEntity=Network::class, mappedBy="sites", cascade={"persist"})
     * @Groups({Site::GROUP_UPDATE, Site::GROUP_READ_DEFAULT})
     */
    private $networks;

    /**
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="create")
     * @Groups({Site::GROUP_READ_DEFAULT})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="update")
     * @Groups({Site::GROUP_READ_DEFAULT})
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=TemplateModel::class)
     * @Groups({Site::GROUP_READ_DEFAULT, Site::GROUP_UPDATE})
     * @ApiFilter(ExistsFilter::class, properties={
     *     "templateModel"
     *})
     */
    private $templateModel;

    /**
     * @var bool Enable the site
     *
     * @ORM\Column(type="boolean")
     * @ApiFilter(BooleanFilter::class)
     * @Groups({Site::GROUP_READ_DEFAULT, Site::GROUP_UPDATE, Site::GROUP_UPDATE_ENABLED, Site::GROUP_UPDATE_DISABLED, Site::GROUP_READ_ITEM})
     */
    private bool $enabled = true;

    public function __construct()
    {
        $this->contact = new Contact();
        $this->address = new Address();
        $this->devices = new ArrayCollection();
        $this->screens = new ArrayCollection();
        $this->networks = new ArrayCollection();
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

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(string $externalId): self
    {
        $this->externalId = trim($externalId);

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

    public function getContact(): Contact
    {
        return $this->contact;
    }

    public function setContact(Contact $contact): self
    {
        $this->contact = $contact;

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
            $device->setSite($this);
        }

        return $this;
    }

    public function removeDevice(Device $device): self
    {
        if ($this->devices->removeElement($device)) {
            // set the owning side to null (unless already changed)
            if ($device->getSite() === $this) {
                $device->setSite(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Screen[]
     */
    public function getScreens(): Collection
    {
        return $this->screens;
    }

    public function addScreen(Screen $screen): self
    {
        if (!$this->screens->contains($screen)) {
            $this->screens[] = $screen;
            $screen->setSite($this);
        }

        return $this;
    }

    public function removeScreen(Screen $screen): self
    {
        if ($this->screens->removeElement($screen)) {
            // set the owning side to null (unless already changed)
            if ($screen->getSite() === $this) {
                $screen->setSite(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Network[]
     */
    public function getNetworks(): Collection
    {
        return $this->networks;
    }

    public function addNetworks(Network $network): self
    {
        if (!$this->networks->contains($network)) {
            $this->networks[] = $network;
            $network->addSite($this);
        }

        return $this;
    }

    public function removeNetworks(Network $network): self
    {
        if ($this->networks->removeElement($network)) {
            $network->removeSite($this);
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

    public function getTemplateModel(): ?TemplateModel
    {
        return $this->templateModel;
    }

    public function setTemplateModel(?TemplateModel $templateModel): self
    {
        $this->templateModel = $templateModel;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address): void
    {
        $this->address = $address;
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

    public function addNetwork(Network $network): self
    {
        if (!$this->networks->contains($network)) {
            $this->networks[] = $network;
            $network->addSite($this);
        }

        return $this;
    }

    public function removeNetwork(Network $network): self
    {
        if ($this->networks->removeElement($network)) {
            $network->removeSite($this);
        }

        return $this;
    }


}

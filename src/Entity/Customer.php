<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Controller\CustomerCustomController;
use App\Interfaces\UserGroupVisibilityInterface;
use App\Repository\CustomerRepository;
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
 * Customer
 * @ApiFilter(PropertyFilter::class)
 * @ApiResource(
 *     normalizationContext=Customer::API_READ,
 *     denormalizationContext=Customer::API_UPDATE,
 *     collectionOperations={
 *         "get"={"security"="is_granted('ROLE_CUSTOMER_GET_COLLECTION')"},
 *         "post"={"denormalization_context"=Customer::API_CREATE,
 *                "security_post_denormalize" = "is_granted('ROLE_CUSTOMER_POST_COLLECTION', object)"}
 *     },
 *     itemOperations={
 *         "get"={"normalization_context"=Customer::API_READ_DETAIL,  "security"="is_granted('ROLE_CUSTOMER_GET_ITEM', object)"},
 *         "delete"={"security"="is_granted('ROLE_CUSTOMER_DELETE_ITEM', object)"},
 *         "put"={"denormalization_context"=Customer::API_UPDATE,  "security"="is_granted('ROLE_CUSTOMER_PUT_ITEM', object)"},
 *         "patch"={ "security"="is_granted('ROLE_CUSTOMER_PATCH_ITEM', object)"},
 *         "api_customers_patch_enable" = {
 *             "route_name"="api_customers_patch_enable",
 *             "controller"=CustomerCustomController::class,
 *             "method"="PATCH",
 *             "path"="/customers/{code}/enable",
 *             "denormalization_context"= Customer::API_PATCH_ENABLED,
 *             "normalization_context"= Customer::API_PATCH_ENABLED,
 *             "openapi_context" = {
 *                 "summary" = "Set the Customer as enabled true",
 *                 "description" = "Updates only the property **enabled** of a Customer and set it to `true`."
 *             }
 *         },
 *         "api_customers_patch_disable" = {
 *             "route_name"="api_customers_patch_disable",
 *             "controller"=CustomerCustomController::class,
 *             "method"="PATCH",
 *             "path"="/customers/{code}/disable",
 *             "denormalization_context"= Customer::API_PATCH_DISABLED,
 *             "normalization_context"= Customer::API_PATCH_DISABLED,
 *             "openapi_context" = {
 *                 "summary" = "Set the Customer as enabled false",
 *                 "description" = "Updates only the property **enabled** of a Customer and set it to `false`."
 *             }
 *         },
 *         "patch_name" = {
 *             "method"="PATCH",
 *             "path"="/customers/{code}/name",
 *             "format"= "jsonld",
 *             "security"="is_granted('ROLE_CUSTOMER_PATCH_NAME')",
 *             "denormalization_context"= Customer::API_PATCH_NAME,
 *             "normalization_context"= Customer::API_PATCH_NAME,
 *             "openapi_context" = {
 *                 "summary" = "Updates only the name and description of a Customer",
 *                 "description" = "Updates only the  **name** and the **description** of a Customer. Both fields don't need to be filled at the same time"
 *             }
 *         },
 *     }
 * )
 * @ORM\Entity(repositoryClass=CustomerRepository::class)
 * @ORM\EntityListeners({"App\Doctrine\CustomerListener"})
 * @UniqueEntity("code")
 */
class Customer implements UserGroupVisibilityInterface
{
    public const GROUP_CREATE = 'customer:create';
    public const GROUP_READ_DEFAULT = 'customer:read_default';
    public const GROUP_READ_COLLECTION = 'customer:read_list';
    public const GROUP_READ_ITEM = 'customer:read_detail';
    public const GROUP_UPDATE = 'customer:write';
    public const GROUP_UPDATE_ENABLED = 'customer:write_enabled';
    public const GROUP_UPDATE_DISABLED = 'customer:write_disabled';
    public const GROUP_UPDATE_NAME = 'customer:write_name';

    public const API_CREATE = [
        'swagger_definition_name' => 'Create',
        'groups' => [
            Customer::GROUP_CREATE,
            Customer::GROUP_UPDATE,
        ],
    ];
    public const API_READ = [
        'swagger_definition_name' => 'Read',
        'groups' => [
            Customer::GROUP_READ_DEFAULT,
            Customer::GROUP_READ_COLLECTION,
            UserGroup::GROUP_READ_DEFAULT,
        ],
    ];
    public const API_READ_DETAIL = [
        'swagger_definition_name' => 'Detail',
        'groups' => [
            Customer::GROUP_READ_DEFAULT,
            Customer::GROUP_READ_COLLECTION,
            Customer::GROUP_READ_ITEM,
            UserGroup::GROUP_READ_DEFAULT,
        ],
    ];
    public const API_UPDATE = [
        'swagger_definition_name' => 'Update',
        'groups' => [
            Customer::GROUP_UPDATE,
        ],
    ];

    public const API_PATCH_DISABLED = [
        'swagger_definition_name' => 'Update disabled',
        'groups' => [
            Customer::GROUP_UPDATE_DISABLED
        ],
    ];

    public const API_PATCH_ENABLED = [
        'swagger_definition_name' => 'Update enabled',
        'groups' => [
            Customer::GROUP_UPDATE_ENABLED
        ],
    ];

    public const API_PATCH_NAME = [
        'swagger_definition_name' => 'Update name',
        'groups' => [
            Customer::GROUP_UPDATE_NAME,
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
     * @Groups({Customer::GROUP_READ_DEFAULT, Customer::GROUP_CREATE})
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     * @ApiFilter(SearchFilter::class, strategy="partial")
     * @Groups({Customer::GROUP_READ_DEFAULT, Customer::GROUP_UPDATE, Customer::GROUP_UPDATE_NAME})
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
     * @Groups({Customer::GROUP_READ_DEFAULT, Customer::GROUP_UPDATE, Customer::GROUP_UPDATE_NAME})
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=UserGroup::class, mappedBy="customer", orphanRemoval=true)
     * @Groups({Customer::GROUP_READ_DEFAULT, Customer::GROUP_UPDATE})
     */
    private $userGroups;

    /**
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="create")
     * @Groups({Customer::GROUP_READ_DEFAULT})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="update")
     * @Groups({Customer::GROUP_READ_DEFAULT})
     */
    private $updatedAt;

    /**
     * @var bool Enable the Customer
     *
     * @ORM\Column(type="boolean")
     * @ApiFilter(BooleanFilter::class)
     * @Groups({Customer::GROUP_READ_DEFAULT, Customer::GROUP_UPDATE, Customer::GROUP_UPDATE_ENABLED, Customer::GROUP_UPDATE_DISABLED})
     */
    private $enabled = true;

    public function __construct()
    {
        $this->userGroups = new ArrayCollection();
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
     * @return Collection|UserGroup[]
     */
    public function getUserGroups(): Collection
    {
        return $this->userGroups;
    }

    public function addUserGroup(UserGroup $userGroup): self
    {
        if (!$this->userGroups->contains($userGroup)) {
            $this->userGroups[] = $userGroup;
            $userGroup->setCustomer($this);
        }

        return $this;
    }

    public function removeUserGroup(UserGroup $userGroup): self
    {
        if ($this->userGroups->removeElement($userGroup)) {
            // set the owning side to null (unless already changed)
            if ($userGroup->getCustomer() === $this) {
                $userGroup->setCustomer(null);
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

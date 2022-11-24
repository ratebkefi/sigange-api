<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Repository\UserGroupRepository;
use App\Validator\ParentConstraint;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * UserGroup
 * @ApiFilter(PropertyFilter::class)
 * @ApiFilter (OrderFilter::class, properties={
 *     "code",
 *     "name",
 *     "customer.name",
 *     "createdAt",
 *     "updatedAt"
 *     })
 * @ApiResource(
 *     normalizationContext=UserGroup::API_READ,
 *     denormalizationContext=UserGroup::API_UPDATE,
 *     collectionOperations={
 *         "get"={"security"="is_granted('ROLE_USER_GROUP_GET_COLLECTION')"},
 *         "post"={"denormalization_context"=UserGroup::API_CREATE,
 *                 "security_post_denormalize" = "is_granted('ROLE_USER_GROUP_POST_COLLECTION', object)"}
 *     },
 *     itemOperations={
 *         "get"={"normalization_context"=UserGroup::API_READ_DETAIL, "security"="is_granted('ROLE_USER_GROUP_GET_ITEM', object)"},
 *         "delete"={"security"="is_granted('ROLE_USER_GROUP_DELETE_ITEM', object)"},
 *         "put"={"denormalization_context"=UserGroup::API_UPDATE, "security"="is_granted('ROLE_USER_GROUP_PUT_ITEM', object)"},
 *         "patch"={"security"="is_granted('ROLE_USER_GROUP_PATCH_ITEM', object)"},
 *     }
 * )
 * @ORM\Entity(repositoryClass=UserGroupRepository::class)
 * @UniqueEntity("code")
 */
class UserGroup
{

    public const UUID_NAMESPACE = '01729310-c5d0-4357-ae85-a3560b552dda';

    public const GROUP_CREATE = 'user_group:create';
    public const GROUP_READ_DEFAULT = 'user_group:read_default';
    public const GROUP_READ_COLLECTION = 'user_group:read_list';
    public const GROUP_READ_ITEM = 'user_group:read_detail';
    public const GROUP_UPDATE = 'user_group:write';

    public const API_CREATE = [
        'swagger_definition_name' => 'Create',
        'groups' => [
            UserGroup::GROUP_CREATE,
            UserGroup::GROUP_UPDATE,
        ],
    ];
    public const API_READ = [
        'swagger_definition_name' => 'Read',
        'groups' => [
            UserGroup::GROUP_READ_DEFAULT,
            UserGroup::GROUP_READ_COLLECTION,
            Customer::GROUP_READ_DEFAULT,
            UserRole::GROUP_READ_DEFAULT
        ],
    ];
    public const API_READ_DETAIL = [
        'swagger_definition_name' => 'Detail',
        'groups' => [
            UserGroup::GROUP_READ_DEFAULT,
            UserGroup::GROUP_READ_COLLECTION,
            UserGroup::GROUP_READ_ITEM,
            Customer::GROUP_READ_DEFAULT,
            UserRole::GROUP_READ_DEFAULT
        ],
    ];
    public const API_UPDATE = [
        'swagger_definition_name' => 'Update',
        'groups' => [
            UserGroup::GROUP_UPDATE,
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
     * @ORM\Column(type="uuid", unique=true)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_EXACT)
     * @ApiProperty(identifier=true)
     * @Groups({UserGroup::GROUP_READ_DEFAULT, UserGroup::GROUP_CREATE})
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_PARTIAL)
     * @Groups({UserGroup::GROUP_READ_DEFAULT, UserGroup::GROUP_UPDATE})
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
     * @Groups({UserGroup::GROUP_READ_DEFAULT, UserGroup::GROUP_UPDATE})
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity=UserRole::class)
     * @Groups({UserGroup::GROUP_READ_DEFAULT, UserGroup::GROUP_UPDATE})
     */
    private $userRoles;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class, inversedBy="userGroups")
     * @ApiFilter(SearchFilter::class, properties={
     *     "customer.name": SearchFilter::STRATEGY_PARTIAL
     * })
     * @Groups({UserGroup::GROUP_READ_DEFAULT, UserGroup::GROUP_UPDATE})
     */
    private $customer;

    /**
     * @ParentConstraint()
     * @ORM\ManyToOne(targetEntity=UserGroup::class, inversedBy="children")
     * @Groups({UserGroup::GROUP_READ_ITEM, UserGroup::GROUP_UPDATE})
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity=UserGroup::class, mappedBy="parent")
     * @Groups({UserGroup::GROUP_READ_ITEM, UserGroup::GROUP_UPDATE})
     */
    private $children;

    /**
     * @ORM\OneToMany(targetEntity=ApiToken::class, mappedBy="userGroup")
     * @Groups({User::GROUP_READ_ITEM})
     */
    private $apiToken;

    /**
     * @var DateTime Creation date
     *
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="create")
     * @Groups({UserGroup::GROUP_READ_DEFAULT})
     */
    private $createdAt;

    /**
     * @var DateTime Last update date
     *
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="update")
     * @Groups({UserGroup::GROUP_READ_DEFAULT})
     */
    private $updatedAt;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="groups")
     * @Groups({UserGroup::GROUP_READ_ITEM, UserGroup::GROUP_UPDATE})
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity=EntityDisplayCustomization::class, mappedBy="sharedWith")
     * @Groups({UserGroup::GROUP_READ_DEFAULT})
     */
    private $entityDisplayCustomizations;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->userRoles = new ArrayCollection();
        $this->entityDisplayCustomizations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
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
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = array_map(function (UserRole $userRole) {
            return $userRole->getRoleName();
        }, $this->getUserRoles()->toArray());

        return array_unique($roles);
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
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

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addGroup($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeGroup($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return strval($this->getCode());
    }

    /**
     * @return Collection
     */
    public function getUserRoles(): Collection
    {
        return $this->userRoles;
    }

    /**
     * @param Collection $userRoles
     * @return UserGroup
     */
    public function setUserRoles(Collection $userRoles): UserGroup
    {
        $this->userRoles = $userRoles;
        return $this;
    }

    public function addUserRole(UserRole $userRole): self
    {
        if (!$this->userRoles->contains($userRole)) {
            $this->userRoles[] = $userRole;
        }

        return $this;
    }

    public function removeUserRole(UserRole $userRole): self
    {
        $this->userRoles->removeElement($userRole);

        return $this;
    }

    /**
     * @return Collection
     */
    public function getApiToken(): Collection
    {
        return $this->apiToken;
    }


}

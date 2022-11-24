<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Controller\UserCustomController;
use App\Interfaces\UserGroupVisibilityInterface;
use App\Repository\UserRepository;
use App\Validator\AdminConstraint;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *  User
 * @ApiFilter(PropertyFilter::class)
 * @ApiFilter (OrderFilter::class, properties={
 *     "code",
 *     "username",
 *     "email",
 *     "createdAt",
 *     "updatedAt"
 *     })
 * @ApiResource(
 *     normalizationContext=User::API_READ,
 *     denormalizationContext=User::API_UPDATE,
 *     collectionOperations={
 *         "get"={"security"="is_granted('ROLE_USER_GET_COLLECTION')"},
 *         "post"={"denormalization_context"=User::API_CREATE,
 *                 "security_post_denormalize" = "is_granted('ROLE_USER_POST_COLLECTION', object)"}
 *     },
 *     itemOperations={
 *         "get"={"normalization_context"=User::API_READ_DETAIL, "security"="is_granted('ROLE_USER_GET_ITEM', object)"},
 *         "delete"={"security"="is_granted('ROLE_USER_DELETE_ITEM', object)"},
 *         "put"={"denormalization_context"=User::API_UPDATE, "security"="is_granted('ROLE_USER_PUT_ITEM', object)"},
 *         "patch"={"security"="is_granted('ROLE_USER_PATCH_ITEM', object)"},
 *         "patch_set_is_super_admin" = {
 *             "controller"=UserCustomController::class,
 *             "method"="PATCH",
 *             "path"="/users/{code}/set_is_super_admin",
 *             "denormalization_context"= User::API_PATCH_SET_SUPER_ADMIN,
 *             "openapi_context" = {
 *                 "summary" = "Sets User as super admin"
 *             }
 *         },
 *         "patch_unset_is_super_admin" = {
 *             "controller"=UserCustomController::class,
 *             "method"="PATCH",
 *             "path"="/users/{code}/unset_is_super_admin",
 *             "denormalization_context"= User::API_PATCH_UNSET_SUPER_ADMIN,
 *             "openapi_context" = {
 *                 "summary" = "Revokes super admin status to User"
 *             }
 *         },
 *     }
 * )
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity("email")
 * @UniqueEntity("code")
 * @UniqueEntity("username")
 */
class User implements UserInterface, UserGroupVisibilityInterface
{
    public const GROUP_CREATE = 'user:create';
    public const GROUP_READ_DEFAULT = 'user:read_default';
    public const GROUP_READ_COLLECTION = 'user:read_list';
    public const GROUP_READ_ITEM = 'user:read_detail';
    public const GROUP_UPDATE = 'user:write';
    public const GROUP_UPDATE_SET_SUPER_ADMIN = 'user:write_set_super_admin';
    public const GROUP_UPDATE_UNSET_SUPER_ADMIN = 'video_stream:write_unset_super_admin';

    public const API_CREATE = [
        'swagger_definition_name' => 'Create',
        'groups' => [
            User::GROUP_CREATE,
            User::GROUP_UPDATE,
        ],
    ];
    public const API_READ = [
        'swagger_definition_name' => 'Read',
        'groups' => [
            User::GROUP_READ_DEFAULT,
            User::GROUP_READ_COLLECTION,
            UserGroup::GROUP_READ_DEFAULT,
            UserRole::GROUP_READ_DEFAULT
        ],
    ];
    public const API_READ_DETAIL = [
        'swagger_definition_name' => 'Detail',
        'groups' => [
            User::GROUP_READ_DEFAULT,
            User::GROUP_READ_COLLECTION,
            User::GROUP_READ_ITEM,
            UserGroup::GROUP_READ_DEFAULT,
            UserRole::GROUP_READ_DEFAULT,
            EntityDisplayCustomization::GROUP_READ_DEFAULT,
        ],
    ];
    public const API_UPDATE = [
        'swagger_definition_name' => 'Update',
        'groups' => [
            User::GROUP_UPDATE,
        ],
    ];

    public const API_PATCH_SET_SUPER_ADMIN = [
        'swagger_definition_name' => 'Set user as super admin',
        'groups' => [
            User::GROUP_UPDATE_SET_SUPER_ADMIN
        ],
    ];

    const API_PATCH_UNSET_SUPER_ADMIN = [
        'swagger_definition_name' => 'Remove super admin status for user',
        'groups' => [
            User::GROUP_UPDATE_UNSET_SUPER_ADMIN
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
     * @ORM\Column(type="string", length=180)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_EXACT)
     * @Groups({User::GROUP_READ_DEFAULT, User::GROUP_UPDATE})
     * @Assert\Length(
     *     min=1,
     *     max=180,
     *     maxMessage="Maximum number of characters is 180",
     *     minMessage="Minimum number of characters is 1"
     * )
     */
    private $email;

    /**
     * @var Uuid External identifier used by API
     * @ORM\Column(type="uuid", unique=true)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_EXACT)
     * @ApiProperty(identifier=true)
     * @Groups({User::GROUP_READ_DEFAULT, User::GROUP_CREATE})
     */
    private $code;

    /**
     * @ORM\ManyToMany(targetEntity=UserRole::class)
     * @Groups({User::GROUP_READ_DEFAULT, User::GROUP_UPDATE})
     * @AdminConstraint()
     */
    private $userRoles;

    /**
     * @var bool Is user an admin
     *
     * @ORM\Column(type="boolean", options={"default":false})
     * @Groups({User::GROUP_READ_DEFAULT, User::GROUP_UPDATE_UNSET_SUPER_ADMIN, User::GROUP_UPDATE_SET_SUPER_ADMIN})
     */
    private bool $isSuperAdmin = false;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     *
     */
    private $password;

    /**
     * @var string The plain password to be hashed
     * @Groups({User::GROUP_UPDATE})
     */
    private $plainPassword;

    /**
     * @ORM\OneToMany(targetEntity=ApiToken::class, mappedBy="user")
     * @Groups({User::GROUP_READ_ITEM})
     */
    private $apiToken;


    /**
     * @ORM\Column(type="string", length=255)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_EXACT)
     * @Groups({User::GROUP_READ_DEFAULT, User::GROUP_UPDATE})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     maxMessage="Maximum number of characters is 255",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $username;

    /**
     * @ORM\ManyToMany(targetEntity=UserGroup::class, inversedBy="users")
     * @Groups({User::GROUP_READ_DEFAULT, User::GROUP_UPDATE})
     * @AdminConstraint()
     */
    private $groups;

    /**
     * @ORM\OneToMany(targetEntity=EntityDisplayCustomization::class, mappedBy="owner")
     * @Groups({User::GROUP_READ_ITEM})
     */
    private $entityDisplayCustomizations;

    /**
     * @var DateTime Creation date
     *
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="create")
     * @Groups({User::GROUP_READ_DEFAULT})
     */
    private $createdAt;

    /**
     * @var DateTime Last update date
     *
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="update")
     * @Groups({User::GROUP_READ_DEFAULT})
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $activatedToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $activatedTokenAt;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
        $this->userRoles = new ArrayCollection();
        $this->entityDisplayCustomizations = new ArrayCollection();
        $this->isSuperAdmin = false;
        $this->apiToken = new ArrayCollection();

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = trim($email);

        return $this;
    }

    /**
     * A User inherits all UserRoles from its UserGroups
     * Super admin inherits from admin role
     * @return array of string , List of all user's UserRole roleName and user's userGroups' roleName
     * @see UserInterface
     */
    public function getRoles(): array
    {
        if ($this->isSuperAdmin) {
            return ['ROLE_SUPER_ADMIN', 'ROLE_ADMIN'];
        }

        $roles = array_map(function (UserRole $userRole) {
            return $userRole->getRoleName();
        }, $this->getUserRoles()->toArray());
        //FIXME a UserGroup has parent and children, has to determine the business logic of UserRole inheritance
        foreach ($this->getGroups() as $userGroup) {
            foreach ($userGroup->getRoles() as $role) {
                $roles[] = $role;
            }
        }
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @return Collection
     */
    public function getUserRoles(): Collection
    {
        return $this->userRoles;
    }

    public function getGroups(): Collection
    {
        return $this->groups;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function addGroup(UserGroup $group): self
    {
        if (!$this->groups->contains($group)) {
            $this->groups[] = $group;
        }

        return $this;
    }

    public function removeGroup(UserGroup $group): self
    {
        $this->groups->removeElement($group);

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
     *
     * @param UserRole $userRole
     * @return $this
     */
    public function addUserRole(UserRole $userRole): self
    {
        if (!$this->userRoles->contains($userRole)) {
            $this->userRoles[] = $userRole;
        }

        return $this;
    }

    /**
     *
     * @param UserRole $userRole
     * @return $this
     */
    public function removeUserRole(UserRole $userRole): self
    {
        $this->userRoles->removeElement($userRole);

        return $this;
    }

    /**
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->isSuperAdmin;
    }

    /**
     * @return bool
     */
    public function getIsSuperAdmin(): bool
    {
        return $this->isSuperAdmin;
    }

    /**
     * @param bool $isSuperAdmin
     * @return User
     */
    public function setIsSuperAdmin(bool $isSuperAdmin): User
    {
        $this->isSuperAdmin = $isSuperAdmin;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getUserGroups(): Collection
    {
        return $this->getGroups();
    }


    /**
     * @return Collection
     */
    public function getEntityDisplayCustomizations(): Collection
    {
        return $this->entityDisplayCustomizations;
    }

    /**
     * @return ?string
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     * @return User
     */
    public function setPlainPassword(string $plainPassword): User
    {
        $this->updatedAt = new DateTime(); // to trigger preUpdate
        $this->plainPassword = trim($plainPassword);
        return $this;
    }

    public function getActivatedToken(): ?string
    {
        return $this->activatedToken;
    }

    public function setActivatedToken(?string $activatedToken): self
    {
        $this->activatedToken = trim($activatedToken);

        return $this;
    }

    public function getActivatedTokenAt(): ?\DateTimeInterface
    {
        return $this->activatedTokenAt;
    }

    public function setActivatedTokenAt(?\DateTimeInterface $activatedTokenAt): self
    {
        $this->activatedTokenAt = $activatedTokenAt;

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

<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Repository\UserRoleRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * UserRole
 * @ApiFilter(PropertyFilter::class)
 * @ApiResource(
 *     attributes={
 *     "order"={"roleName": "ASC"},
 *     "pagination_client_enabled"=true
 *     },
 *     normalizationContext=UserRole::API_READ,
 *     denormalizationContext=UserRole::API_UPDATE,
 *     collectionOperations={
 *         "get"={"security"="is_granted('ROLE_USER_ROLE_GET_COLLECTION')"},
 *         "post"={"denormalization_context"=UserRole::API_CREATE,
 *                 "security_post_denormalize" = "is_granted('ROLE_USER_ROLE_POST_COLLECTION', object)"}
 *     },
 *     itemOperations={
 *         "get"={"normalization_context"=UserRole::API_READ_DETAIL, "security"="is_granted('ROLE_USER_ROLE_GET_ITEM', object)"},
 *         "delete"={"security"="is_granted('ROLE_USER_ROLE_DELETE_ITEM', object)"},
 *         "put"={"denormalization_context"=UserRole::API_UPDATE, "security"="is_granted('ROLE_USER_ROLE_PUT_ITEM', object)"},
 *         "patch"={"security"="is_granted('ROLE_USER_ROLE_PATCH_ITEM', object)"},
 *     }
 * )
 * @ORM\Entity(repositoryClass=UserRoleRepository::class)
 * @UniqueEntity("code")
 */
class UserRole
{

    public const UUID_NAMESPACE = '1a8737cd-2358-46b1-b98a-165372cb96d9';
    public const GROUP_CREATE = 'userRole:create';
    public const GROUP_READ_DEFAULT = 'userRole:read_default';
    const GROUP_READ_COLLECTION = 'userRole:read_list';
    const GROUP_READ_ITEM = 'userRole:read_detail';
    const GROUP_UPDATE = 'userRole:write';

    const API_CREATE = [
        'swagger_definition_name' => 'Create',
        'groups' => [
            UserRole::GROUP_CREATE,
            UserRole::GROUP_UPDATE,
        ],
    ];
    const API_READ = [
        'swagger_definition_name' => 'Read',
        'groups' => [
            UserRole::GROUP_READ_DEFAULT,
            UserRole::GROUP_READ_COLLECTION,
        ],
    ];
    const API_READ_DETAIL = [
        'swagger_definition_name' => 'Detail',
        'groups' => [
            UserRole::GROUP_READ_DEFAULT,
            UserRole::GROUP_READ_COLLECTION,
            UserRole::GROUP_READ_ITEM,
        ],
    ];
    const API_UPDATE = [
        'swagger_definition_name' => 'Update',
        'groups' => [
            UserRole::GROUP_UPDATE,
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
    private int $id;

    /**
     * @var Uuid External identifier used by API
     * @ORM\Column(type="uuid", unique=true)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_EXACT)
     * @ApiProperty(identifier=true)
     * @Groups({UserRole::GROUP_READ_DEFAULT, UserRole::GROUP_CREATE})
     */
    private Uuid $code;

    /**
     * Role name using symfony convention to be used by security service, voters, etc ...(ROLE_XXX_YYY)
     * @ORM\Column(type="string", length=255)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_PARTIAL)
     * @Groups({UserRole::GROUP_READ_DEFAULT, UserRole::GROUP_UPDATE})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     maxMessage="Maximum number of characters is 255",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private string $roleName;

    /**
     * User friendly role name
     * @ORM\Column(type="string", length=255)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_PARTIAL)
     * @Groups({UserRole::GROUP_READ_DEFAULT, UserRole::GROUP_UPDATE})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     maxMessage="Maximum number of characters is 255",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private string $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({UserRole::GROUP_READ_DEFAULT, UserRole::GROUP_UPDATE})
     */
    private ?string $description;

    /**
     * @var DateTime Creation date
     *
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="create")
     * @Groups({UserRole::GROUP_READ_DEFAULT})
     */
    private $createdAt;

    /**
     * @var DateTime Last update date
     *
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="update")
     * @Groups({UserRole::GROUP_READ_DEFAULT})
     */
    private $updatedAt;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Uuid
     */
    public function getCode(): ?Uuid
    {
        return $this->code;
    }

    /**
     * @param Uuid $code
     * @return UserRole
     */
    public function setCode(Uuid $code): UserRole
    {
        $this->code = $code;
        return $this;
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return UserRole
     */
    public function setName(string $name): UserRole
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param ?string $description
     * @return UserRole
     */
    public function setDescription(?string $description): UserRole
    {
        $this->description = $description;
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
     * @return string
     */
    public function getRoleName(): string
    {
        return $this->roleName;
    }

    /**
     * @param string $roleName
     * @return UserRole
     */
    public function setRoleName(string $roleName): UserRole
    {
        $this->roleName = $roleName;
        return $this;
    }


}

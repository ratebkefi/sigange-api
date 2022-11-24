<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\ApiPlatform\UuidFilter;
use App\Controller\EntityDisplayCustomizationCustomController;
use App\Form\ColumnDefinition;
use App\Repository\EntityDisplayCustomizationRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;


/**
 * Entity display customization
 * @ApiFilter(PropertyFilter::class)
 * @ORM\Entity(repositoryClass=EntityDisplayCustomizationRepository::class)
 * @ApiResource(
 *     normalizationContext=EntityDisplayCustomization::API_READ,
 *     denormalizationContext=EntityDisplayCustomization::API_UPDATE,
 *     itemOperations={
 *         "get"={"normalization_context"=EntityDisplayCustomization::API_READ_DETAIL,
 *             "security"="is_granted('ROLE_ENTITY_DISPLAY_CUSTOMIZATION_GET_ITEM') and object.getOwner() == user"
 *         },
 *         "api_entity_display_customizations_patch_custom" = {
 *             "route_name"="api_entity_display_customizations_patch_custom",
 *             "controller"=EntityDisplayCustomizationCustomController::class,
 *             "method"="PATCH",
 *             "path"="/entity_display_customizations/{code}",
 *             "denormalization_context"= EntityDisplayCustomization::API_UPDATE,
 *             "normalization_context"= EntityDisplayCustomization::API_UPDATE,
 *             "openapi_context" = {
 *                 "summary" = "Updates the EntityDisplayCustomization with special treatment",
 *                 "description" = "Updates the EntityDisplayCustomization and, if it is **default** sets **isDefault** to false for all the other the EntityDisplayCustomization of the owner with this entityClassName."
 *             }
 *         },
 *         "delete"={
 *             "security"="is_granted('ROLE_ENTITY_DISPLAY_CUSTOMIZATION_DELETE_ITEM') and object.getOwner() == user"
 *         }
 *     },
 *     collectionOperations={
 *         "api_entity_display_customizations_post_collection_custom" = {
 *             "route_name"="api_entity_display_customizations_post_collection_custom",
 *             "controller"=EntityDisplayCustomizationCustomController::class,
 *             "method"="POST",
 *             "path"="/entity_display_customizations",
 *             "denormalization_context"= EntityDisplayCustomization::API_CREATE,
 *             "normalization_context"= EntityDisplayCustomization::API_CREATE,
 *             "openapi_context" = {
 *                 "summary" = "Creates the EntityDisplayCustomization with special treatment",
 *                 "description" = "Creates the EntityDisplayCustomization and, if it is **default** sets **isDefault** to false for all the other the EntityDisplayCustomization of the owner with this entityClassName."
 *             }
 *         },
 *         "api_entity_display_customizations_put_collection_custom" = {
 *             "route_name"="api_entity_display_customizations_put_collection_custom",
 *             "controller"=EntityDisplayCustomizationCustomController::class,
 *             "method"="PUT",
 *             "path"="/entity_display_customizations/{code}",
 *             "denormalization_context"= EntityDisplayCustomization::API_UPDATE,
 *             "normalization_context"= EntityDisplayCustomization::API_UPDATE,
 *             "openapi_context" = {
 *                 "summary" = "Updates the EntityDisplayCustomization with special treatment",
 *                 "description" = "Updates the EntityDisplayCustomization and, if it is **default** sets **isDefault** to false for all the other the EntityDisplayCustomization of the owner  with this entityClassName."
 *             }
 *         },
 *         "get"={
 *             "security"="is_granted('ROLE_ENTITY_DISPLAY_CUSTOMIZATION_GET_COLLECTION')"
 *         }
 *     }
 * )
 * @UniqueEntity("code")
 */
class EntityDisplayCustomization
{
    public const GROUP_CREATE = 'entity_display_customization:create';
    public const GROUP_READ_DEFAULT = 'entity_display_customization:read_default';
    public const GROUP_READ_COLLECTION = 'entity_display_customization:read_list';
    public const GROUP_READ_ITEM = 'entity_display_customization:read_detail';
    public const GROUP_UPDATE = 'entity_display_customization:write';

    public const API_CREATE = [
        'swagger_definition_name' => 'Create',
        'groups' => [
            EntityDisplayCustomization::GROUP_CREATE,
            EntityDisplayCustomization::GROUP_UPDATE,
        ],
    ];
    public const API_READ = [
        'swagger_definition_name' => 'Read',
        'groups' => [
            EntityDisplayCustomization::GROUP_READ_DEFAULT,
            EntityDisplayCustomization::GROUP_READ_COLLECTION,
            EntityDisplayCustomization::GROUP_READ_DEFAULT,
        ],
    ];
    public const API_READ_DETAIL = [
        'swagger_definition_name' => 'Detail',
        'groups' => [
            EntityDisplayCustomization::GROUP_READ_DEFAULT,
            EntityDisplayCustomization::GROUP_READ_COLLECTION,
            EntityDisplayCustomization::GROUP_READ_ITEM,
        ],
    ];
    public const API_UPDATE = [
        'swagger_definition_name' => 'Update',
        'groups' => [
            EntityDisplayCustomization::GROUP_UPDATE,
        ],
    ];


    /**
     * EntityDisplayCustomization constructor.
     */
    public function __construct()
    {
    }

    /**
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
     * @Groups({EntityDisplayCustomization::GROUP_READ_DEFAULT, EntityDisplayCustomization::GROUP_CREATE})
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({EntityDisplayCustomization::GROUP_READ_DEFAULT, EntityDisplayCustomization::GROUP_UPDATE})
     */
    private $name;

    /**
     * @Groups({EntityDisplayCustomization::GROUP_READ_DEFAULT, EntityDisplayCustomization::GROUP_UPDATE})
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var  ColumnDefinition[] // TODO array
     * @ORM\Column(type="json")
     * @Groups({EntityDisplayCustomization::GROUP_READ_DEFAULT, EntityDisplayCustomization::GROUP_UPDATE})
     * @example([ {"label": "description","propertyName": "description","propertyType": "string"},{"label": "url","propertyName": "url","propertyType": "string"}])
     */
    private $columns = [];

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({EntityDisplayCustomization::GROUP_READ_DEFAULT, EntityDisplayCustomization::GROUP_CREATE})
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_EXACT)
     */
    private $entityClassName;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({EntityDisplayCustomization::GROUP_READ_DEFAULT, EntityDisplayCustomization::GROUP_UPDATE})
     */
    private $isDefault;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="entityDisplayCustomizations")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({EntityDisplayCustomization::GROUP_READ_DEFAULT, EntityDisplayCustomization::GROUP_CREATE})
     * @ApiFilter(UuidFilter::class, properties={
     *     "owner.code": SearchFilter::STRATEGY_EXACT,
     * })
     */
    private $owner;


    /**
     * @ORM\ManyToOne(targetEntity=UserGroup::class, inversedBy="entityDisplayCustomizations")
     * @Groups({EntityDisplayCustomization::GROUP_UPDATE})
     * @ORM\JoinColumn(nullable=true)
     */

    private $sharedWith;

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
     * @return ColumnDefinition[]|null
     */
    public function getColumns(): ?array
    {
        return $this->columns;
    }

    /**
     * @param ColumnDefinition[] $columns
     * @return $this
     */
    public function setColumns(array $columns): self
    {
        $this->columns = $columns;
        $columnDefinitions = [];
        if (is_array($columns)) {
            foreach ($columns as $column) {
                $columnDefinitions[] = $column instanceof ColumnDefinition ? $column : new ColumnDefinition($column['label'],
                    $column['propertyName'], $column['propertyType']);
            }
            $this->columns = $columnDefinitions;
            return $this;

        }

        return $this;

    }

    public function getEntityClassName(): ?string
    {
        return $this->entityClassName;
    }

    public function setEntityClassName(string $entityClassName): self
    {
        $this->entityClassName = $entityClassName;

        return $this;
    }

    public function getIsDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;

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
     * @return User
     */
    public function getOwner(): User
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     * @return EntityDisplayCustomization
     */
    public function setOwner(User $owner): self
    {
        $this->owner = $owner;
        return $this;
    }


    /**
     * @return UserGroup
     */
    public function getSharedWith(): ?UserGroup
    {
        return $this->sharedWith;
    }

    /**
     * @param ?UserGroup $sharedWith
     * @return EntityDisplayCustomization
     */
    public function setSharedWith(?UserGroup $sharedWith): self
    {
        $this->sharedWith = $sharedWith;
        return $this;
    }
}

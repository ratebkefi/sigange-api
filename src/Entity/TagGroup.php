<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\ApiPlatform\UuidFilter;
use App\Interfaces\UserGroupVisibilityInterface;
use App\Repository\TagCategoryRepository;
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
 * Tag Group
 * @ApiFilter(PropertyFilter::class)
 * @ApiFilter (OrderFilter::class, properties={
 *     "code",
 *     "name",
 *     "userGroup.name",
 *     "target.name",
 *     "createdAt",
 *     "updatedAt"
 *     })
 * @ApiResource(
 *     normalizationContext=TagGroup::API_READ,
 *     denormalizationContext=TagGroup::API_UPDATE,
 *     collectionOperations={
 *         "get"={"security"="is_granted('ROLE_TAG_GROUP_GET_COLLECTION')"},
 *         "post"={"denormalization_context"=TagGroup::API_CREATE,
 *                 "security_post_denormalize" = "is_granted('ROLE_TAG_GROUP_POST_COLLECTION', object)"}
 *     },
 *     itemOperations={
 *         "get"={"normalization_context"=TagGroup::API_READ_DETAIL, "security"="is_granted('ROLE_TAG_GROUP_GET_ITEM', object)"},
 *         "delete"={"security"="is_granted('ROLE_TAG_GROUP_DELETE_ITEM', object)"},
 *         "put"={"denormalization_context"=TagGroup::API_UPDATE, "security"="is_granted('ROLE_TAG_GROUP_PUT_ITEM', object)"},
 *         "patch"={"security"="is_granted('ROLE_TAG_GROUP_PATCH_ITEM', object)"},
 *     }
 * )
 * @ORM\Entity(repositoryClass=TagCategoryRepository::class)
 * @UniqueEntity("code")
 */
class TagGroup implements UserGroupVisibilityInterface
{
    public const GROUP_CREATE = 'tag_group:create';
    public const GROUP_READ_DEFAULT = 'tag_group:read_default';
    public const GROUP_READ_COLLECTION = 'tag_group:read_list';
    public const GROUP_READ_ITEM = 'tag_group:read_detail';
    public const GROUP_UPDATE = 'tag_group:write';

    public const API_CREATE = [
        'swagger_definition_name' => 'Create',
        'groups' => [
            TagGroup::GROUP_CREATE,
            TagGroup::GROUP_UPDATE,
        ],
    ];
    public const API_READ = [
        'swagger_definition_name' => 'Read',
        'groups' => [
            TagGroup::GROUP_READ_DEFAULT,
            TagGroup::GROUP_READ_COLLECTION,
            Tag::GROUP_READ_DEFAULT,
            TagTarget::GROUP_READ_DEFAULT,
            UserGroup::GROUP_READ_DEFAULT,
        ],
    ];
    public const API_READ_DETAIL = [
        'swagger_definition_name' => 'Detail',
        'groups' => [
            TagGroup::GROUP_READ_DEFAULT,
            TagGroup::GROUP_READ_COLLECTION,
            TagGroup::GROUP_READ_ITEM,
            Tag::GROUP_READ_DEFAULT,
            TagTarget::GROUP_READ_DEFAULT,
            UserGroup::GROUP_READ_DEFAULT,
        ],
    ];
    public const API_UPDATE = [
        'swagger_definition_name' => 'Update',
        'groups' => [
            TagGroup::GROUP_UPDATE,
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
     * @Groups({TagGroup::GROUP_READ_DEFAULT, TagGroup::GROUP_CREATE})
     */
    private $code;

    /**
     * @var string Name of the tag group
     *
     * @ORM\Column(type="string", length=255)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_PARTIAL)
     * @Groups({TagGroup::GROUP_READ_DEFAULT, TagGroup::GROUP_UPDATE})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     maxMessage="Maximum number of characters is 255",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $name;

    /**
     * @var string Description of the tag group
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({TagGroup::GROUP_READ_DEFAULT, TagGroup::GROUP_UPDATE})
     */
    private $description;

    /**
     * @var UserGroup User group of the tag group. If null, the tag group is allowed for all user groups (and customers)
     *
     * @ORM\ManyToOne(targetEntity=UserGroup::class)
     * @Groups({TagGroup::GROUP_READ_DEFAULT, TagGroup::GROUP_CREATE})
     * @ApiFilter(UuidFilter::class, properties={
     *     "userGroup.code": SearchFilter::STRATEGY_EXACT,
     * })
     * @ApiFilter(ExistsFilter::class)
     */
    private $userGroup;

    /**
     * @ORM\Column(type="json")
     * @Groups({TagGroup::GROUP_READ_DEFAULT, TagGroup::GROUP_UPDATE})
     * @Assert\Collection(
     *     fields = {
     *         "multiValueAllowed" = @Assert\Type("boolean"),
     *         "required" = @Assert\Type("boolean"),
     *         "filterable" = @Assert\Type("boolean"),
     *         "newValueAllowed" = @Assert\Type("boolean")
     *     },
     *     allowMissingFields = false,
     *     allowExtraFields = false
     * )
     */
    private $options = [
        'multiValueAllowed' => true,
        'required' => false,
        'filterable' => true,
        'newValueAllowed' => true
    ];

    /**
     * @ORM\ManyToOne(targetEntity=TagTarget::class, inversedBy="tagGroups")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({TagGroup::GROUP_READ_DEFAULT, TagGroup::GROUP_UPDATE})
     * @ApiFilter(SearchFilter::class, properties={
     *     "target.name": SearchFilter::STRATEGY_EXACT,
     * })
     */
    private $target;

    /**
     * @ORM\OneToMany(targetEntity=Tag::class, mappedBy="tagGroup", orphanRemoval=true)
     * @Groups({TagGroup::GROUP_READ_COLLECTION, TagGroup::GROUP_UPDATE, TagGroup::GROUP_READ_DEFAULT})
     */
    private $tags;

    /**
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="create")
     * @Groups({TagGroup::GROUP_READ_DEFAULT})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="update")
     * @Groups({TagGroup::GROUP_READ_DEFAULT})
     */
    private $updatedAt;

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

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
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

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getTarget(): ?TagTarget
    {
        return $this->target;
    }

    public function setTarget(?TagTarget $target): self
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
            $tag->setTagGroup($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->removeElement($tag)) {
            // set the owning side to null (unless already changed)
            if ($tag->getTagGroup() === $this) {
                $tag->setTagGroup(null);
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

    public function getUserGroups(): Collection
    {
        return new ArrayCollection([$this->getUserGroup()]);
    }
}


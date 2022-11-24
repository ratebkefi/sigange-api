<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Interfaces\UserGroupVisibilityInterface;
use App\Repository\TagRepository;
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
 * Tag
 * @ApiFilter(PropertyFilter::class)
 * @ApiResource(
 *     normalizationContext=Tag::API_READ,
 *     denormalizationContext=Tag::API_UPDATE,
 *     collectionOperations={
 *         "get"={"normalization_context"=Tag::API_READ, "security"="is_granted('ROLE_TAG_GET_COLLECTION')"},
 *         "post"={"denormalization_context"=Tag::API_CREATE,
 *                 "security_post_denormalize" = "is_granted('ROLE_TAG_POST_COLLECTION', object)"}
 *     },
 *     itemOperations={
 *         "get"={"normalization_context"=Tag::API_READ_DETAIL, "security"="is_granted('ROLE_TAG_GET_ITEM', object)"},
 *         "delete"={"security"="is_granted('ROLE_TAG_DELETE_ITEM', object)"},
 *         "put"={"denormalization_context"=Tag::API_UPDATE, "security"="is_granted('ROLE_TAG_PUT_ITEM', object)"},
 *         "patch"={"security"="is_granted('ROLE_TAG_PATCH_ITEM', object)"},
 *     }
 * )
 * @ORM\Entity(repositoryClass=TagRepository::class)
 * @UniqueEntity("code")
 */
class Tag implements UserGroupVisibilityInterface
{
    public const GROUP_CREATE = 'tag:create';
    public const GROUP_READ_DEFAULT = 'tag:read_default';
    public const GROUP_READ_COLLECTION = 'tag:read_list';
    public const GROUP_READ_ITEM = 'tag:read_detail';
    public const GROUP_UPDATE = 'tag:write';

    public const GROUP_UPDATE_RELATION = 'taggable:write';

    public const API_CREATE = [
        'swagger_definition_name' => 'Create',
        'groups' => [
            Tag::GROUP_CREATE,
            Tag::GROUP_UPDATE,
        ],
    ];
    public const API_READ = [
        'swagger_definition_name' => 'Read',
        'groups' => [
            Tag::GROUP_READ_DEFAULT,
            Tag::GROUP_READ_COLLECTION,
            TagGroup::GROUP_READ_DEFAULT,
        ],
    ];
    public const API_READ_DETAIL = [
        'swagger_definition_name' => 'Detail',
        'groups' => [
            Tag::GROUP_READ_DEFAULT,
            Tag::GROUP_READ_COLLECTION,
            Tag::GROUP_READ_ITEM,
            TagGroup::GROUP_READ_DEFAULT,
        ],
    ];
    public const API_UPDATE = [
        'swagger_definition_name' => 'Update',
        'groups' => [
            Tag::GROUP_UPDATE,
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
     * @Groups({Tag::GROUP_READ_DEFAULT, Tag::GROUP_CREATE})
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_PARTIAL)
     * @Groups({Tag::GROUP_READ_DEFAULT, Tag::GROUP_UPDATE})
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
     * @Groups({Tag::GROUP_READ_DEFAULT, Tag::GROUP_UPDATE})
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=TagGroup::class, inversedBy="tags")
     * @ORM\JoinColumn(nullable=false)
     * @ApiFilter(SearchFilter::class, properties={
     *     "tagGroup.name": SearchFilter::STRATEGY_PARTIAL
     * })
     * @Groups({Tag::GROUP_READ_DEFAULT, Tag::GROUP_CREATE})
     */
    private $tagGroup;

    /**
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="create")
     * @Groups({Tag::GROUP_READ_DEFAULT})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="update")
     * @Groups({Tag::GROUP_READ_DEFAULT})
     */
    private $updatedAt;

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

    public function getTagGroup(): ?TagGroup
    {
        return $this->tagGroup;
    }

    public function setTagGroup(?TagGroup $tagGroup): self
    {
        $this->tagGroup = $tagGroup;

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
        return $this->tagGroup->getUserGroups();
    }
}

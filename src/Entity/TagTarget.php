<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Repository\TagTargetRepository;
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
 * @ApiFilter(PropertyFilter::class)
 * @ApiResource(
 *     normalizationContext=TagTarget::API_READ,
 *     collectionOperations={
 *         "get"={"security"="is_granted('ROLE_TAG_TARGET_GET_COLLECTION', object)"},
 *     },
 *     itemOperations={
 *         "get"={"normalization_context"=TagTarget::API_READ_DETAIL, "security"="is_granted('ROLE_TAG_TARGET_GET_ITEM', object)"},
 *     }
 * )
 * @ORM\Entity(repositoryClass=TagTargetRepository::class)
 * @UniqueEntity("code")
 */
class TagTarget
{
    public const UUID_NAMESPACE = '98911e36-3585-4ed3-b1f9-4c035f2beda8';

    public const GROUP_READ_DEFAULT = 'device_status:read_default';
    public const GROUP_READ_ITEM = 'device_status:read_item';

    public const API_READ = [
        'swagger_definition_name' => 'Read',
        'groups' => [
            DeviceStatus::GROUP_READ_DEFAULT,
        ],
    ];
    public const API_READ_DETAIL = [
        'swagger_definition_name' => 'Detail',
        'groups' => [
            DeviceStatus::GROUP_READ_DEFAULT,
            DeviceStatus::GROUP_READ_ITEM,
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
     * @Groups({TagTarget::GROUP_READ_DEFAULT})
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=25)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_PARTIAL)
     * @Groups({TagTarget::GROUP_READ_DEFAULT})
     * @Assert\Length(
     *     min=2,
     *     max=25,
     *     maxMessage="Maximum number of characters is 25",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({TagTarget::GROUP_READ_DEFAULT})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"tag:read", "tag_group:read", "tag_target:read", "tag_group:write", "children:read"})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     maxMessage="Maximum number of characters is 255",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $entityClassName;

    /**
     * @ORM\OneToMany(targetEntity=TagGroup::class, mappedBy="target", orphanRemoval=true)
     * @Groups({TagTarget::GROUP_READ_ITEM})
     */
    private $tagGroups;

    /**
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="create")
     * @Groups({TagTarget::GROUP_READ_DEFAULT})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="update")
     * @Groups({TagTarget::GROUP_READ_DEFAULT})
     */
    private $updatedAt;

    public function __construct()
    {
        $this->tagGroups = new ArrayCollection();
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

    public function getEntityClassName(): ?string
    {
        return $this->entityClassName;
    }

    public function setEntityClassName(string $entityClassName): self
    {
        $this->entityClassName = $entityClassName;

        return $this;
    }

    /**
     * @return Collection|TagGroup[]
     */
    public function getTagGroups(): Collection
    {
        return $this->tagGroups;
    }

    public function addTagGroup(TagGroup $tagGroup): self
    {
        if (!$this->tagGroups->contains($tagGroup)) {
            $this->tagGroups[] = $tagGroup;
            $tagGroup->setTarget($this);
        }

        return $this;
    }

    public function removeTagGroup(TagGroup $tagGroup): self
    {
        if ($this->tagGroups->removeElement($tagGroup)) {
            // set the owning side to null (unless already changed)
            if ($tagGroup->getTarget() === $this) {
                $tagGroup->setTarget(null);
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


}

<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Interfaces\UserGroupVisibilityInterface;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use App\Repository\WebhookRepository;
use App\Validator\TagConstraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Webhook
 * @ApiFilter(PropertyFilter::class)
 * @ApiResource(
 *     normalizationContext=Webhook::API_READ,
 *     denormalizationContext=Webhook::API_UPDATE,
 *     collectionOperations={
 *         "get"={"security"="is_granted('ROLE_WEBHOOK_GET_COLLECTION')"},
 *         "post"={"denormalization_context"=Webhook::API_CREATE,
 *                 "security_post_denormalize" = "is_granted('ROLE_WEBHOOK_POST_COLLECTION', object)" }
 *     },
 *     itemOperations={
 *         "get"={"normalization_context"=Webhook::API_READ_DETAIL, "security"="is_granted('ROLE_WEBHOOK_GET_ITEM', object)" },
 *         "delete"={"security"="is_granted('ROLE_WEBHOOK_DELETE_ITEM', object)" },
 *         "put"={"denormalization_context"=Webhook::API_UPDATE, "security"="is_granted('ROLE_WEBHOOK_PUT_ITEM', object)" },
 *         "patch"={"security"="is_granted('ROLE_WEBHOOK_PATCH_ITEM', object)" }
 *     }
 * )
 * @ORM\Entity(repositoryClass=WebhookRepository::class)
 * @UniqueEntity("code")
 */
class Webhook implements UserGroupVisibilityInterface
{
    public const GROUP_CREATE = 'webhook:create';
    public const GROUP_READ_DEFAULT = 'webhook:read_default';
    public const GROUP_READ_COLLECTION = 'webhook:read_list';
    public const GROUP_READ_ITEM = 'webhook:read_detail';
    public const GROUP_UPDATE = 'webhook:write';

    public const API_READ = [
        'swagger_definition_name' => 'Read',
        'groups' => [
            Webhook::GROUP_READ_DEFAULT,
            Webhook::GROUP_READ_COLLECTION,
        ],
    ];
    public const API_READ_DETAIL = [
        'swagger_definition_name' => 'Detail',
        'groups' => [
            Webhook::GROUP_READ_DEFAULT,
            Webhook::GROUP_READ_COLLECTION,
            Webhook::GROUP_READ_ITEM,
        ],
    ];
    public const API_CREATE = [
        'swagger_definition_name' => 'Create',
        'groups' => [
            Webhook::GROUP_CREATE,
            Webhook::GROUP_UPDATE,
        ],
    ];
    public const API_UPDATE = [
        'swagger_definition_name' => 'Update',
        'groups' => [
            Webhook::GROUP_UPDATE,
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
     * @Groups({Webhook::GROUP_READ_DEFAULT, Webhook::GROUP_CREATE})
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_PARTIAL)
     * @Groups({Webhook::GROUP_READ_DEFAULT, Webhook::GROUP_UPDATE})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     maxMessage="Maximum number of characters is 255",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({Webhook::GROUP_READ_DEFAULT, Webhook::GROUP_UPDATE})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     maxMessage="Maximum number of characters is 255",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({Webhook::GROUP_READ_DEFAULT, Webhook::GROUP_UPDATE})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     maxMessage="Maximum number of characters is 255",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $eventType;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({Webhook::GROUP_READ_DEFAULT, Webhook::GROUP_UPDATE})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     maxMessage="Maximum number of characters is 255",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $resourceClass;

    /**
     * @ORM\ManyToOne(targetEntity=UserGroup::class)
     * @ORM\JoinColumn(nullable=false)
     * @ApiFilter(SearchFilter::class, properties={
     *     "userGroup.name": SearchFilter::STRATEGY_PARTIAL
     * })
     * @Groups({Webhook::GROUP_READ_COLLECTION, Webhook::GROUP_UPDATE})
     */
    private $userGroup;

    /**
     * @var DateTime Creation date
     *
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="create")
     * @Groups({Webhook::GROUP_READ_DEFAULT})
     */
    private $createdAt;

    /**
     * @var DateTime Last update date
     *
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="update")
     * @Groups({Webhook::GROUP_READ_DEFAULT})
     */
    private $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = trim($url);

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

    public function getEventType(): ?string
    {
        return $this->eventType;
    }

    public function setEventType(string $eventType): self
    {
        $this->eventType = $eventType;

        return $this;
    }

    public function getResourceClass(): ?string
    {
        return $this->resourceClass;
    }

    public function setResourceClass(string $resourceClass): self
    {
        $this->resourceClass = $resourceClass;

        return $this;
    }


}

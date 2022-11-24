<?php

namespace App\Entity;


use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Controller\VideoStreamCustomController;
use App\Dto\VideoStreamInput;
use App\Interfaces\TaggableInterface;
use App\Interfaces\UserGroupVisibilityInterface;
use App\Repository\VideoStreamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Video Stream
 * @ApiFilter(PropertyFilter::class)
 * @ApiFilter (OrderFilter::class, properties={
 *     "code",
 *     "name",
 *     "userGroup.name",
 *     "url",
 *     "createdAt",
 *     "updatedAt"
 *     })
 * @ApiResource(
 *     input=VideoStreamInput::class,
 *     normalizationContext=VideoStream::API_READ,
 *     denormalizationContext=VideoStream::API_UPDATE,
 *     collectionOperations={
 *         "get"={"security"="is_granted('ROLE_VIDEO_STREAM_GET_COLLECTION')"},
 *         "post"={"denormalization_context"=VideoStream::API_CREATE,
 *                 "security_post_denormalize" = "is_granted('ROLE_VIDEO_STREAM_POST_COLLECTION', object)"}
 *     },
 *     itemOperations={
 *         "get"={"normalization_context"=VideoStream::API_READ_DETAIL,"security"="is_granted('ROLE_VIDEO_STREAM_GET_ITEM', object)"},
 *         "delete"={"security"="is_granted('ROLE_VIDEO_STREAM_DELETE_ITEM', object)"},
 *         "put"={"denormalization_context"=VideoStream::API_UPDATE, "security"="is_granted('ROLE_VIDEO_STREAM_PUT_ITEM', object)"},
 *         "patch"= {"security"="is_granted('ROLE_VIDEO_STREAM_PATCH_ITEM', object)"},
 *         "api_video_streams_patch_enable" = {
 *             "route_name"="api_video_streams_patch_enable",
 *             "controller"=VideoStreamCustomController::class,
 *             "method"="PATCH",
 *             "path"="/video_streams/{code}/enable",
 *             "denormalization_context" = VideoStream::API_PATCH_ENABLED,
 *             "normalization_context"= VideoStream::API_PATCH_ENABLED,
 *             "openapi_context" = {
 *                 "summary" = "Set the VideoStream as enabled true",
 *                 "description" = "Updates only the property **enabled** of a VideoStream and set it to `true`."
 *             }
 *         },
 *         "api_video_streams_patch_disable" = {
 *             "route_name"="api_video_streams_patch_disable",
 *             "controller"=VideoStreamCustomController::class,
 *             "method"="PATCH",
 *             "path"="/video_streams/{code}/disable",
 *             "denormalization_context" = VideoStream::API_PATCH_DISABLED,
 *             "normalization_context"= VideoStream::API_PATCH_DISABLED,
 *             "openapi_context" = {
 *                 "summary" = "Set the VideoStream as enabled false",
 *                 "description" = "Updates only the property **enabled** of a Platform and set it to `false`."
 *             }
 *         },
 *         "patch_name" = {
 *             "method"="PATCH",
 *             "path"="/video_streams/{code}/name",
 *             "format"= "jsonld",
 *             "security"="is_granted('ROLE_VIDEO_STREAM_PATCH_NAME')",
 *             "denormalization_context"= VideoStream::API_PATCH_NAME,
 *             "normalization_context"= VideoStream::API_PATCH_NAME,
 *             "openapi_context" = {
 *                 "summary" = "Updates only the name and description of a Video overlay",
 *                 "description" = "Updates only the  **name** and the **description** of a Video overlay. Both fields don't need to be filled at the same time"
 *             }
 *         },
 *     }
 * )
 * @ORM\Entity(repositoryClass=VideoStreamRepository::class)
 * @UniqueEntity("code")
 */
class VideoStream implements UserGroupVisibilityInterface, TaggableInterface
{

    use TaggableTrait;

    public const GROUP_CREATE = 'video_stream:create';
    public const GROUP_READ_DEFAULT = 'video_stream:read_default';
    public const GROUP_READ_COLLECTION = 'video_stream:read_list';
    public const GROUP_READ_ITEM = 'video_stream:read_detail';
    public const GROUP_UPDATE = 'video_stream:write';
    public const GROUP_UPDATE_ENABLED = 'video_stream:write_enabled';
    public const GROUP_UPDATE_DISABLED = 'video_stream:write_disabled';
    public const GROUP_UPDATE_NAME = 'video_stream:write_name';

    public const API_CREATE = [
        'swagger_definition_name' => 'Create',
        'groups' => [
            VideoStream::GROUP_CREATE,
            VideoStream::GROUP_UPDATE,
            Tag::GROUP_UPDATE_RELATION
        ],
    ];
    public const API_READ = [
        'swagger_definition_name' => 'Read',
        'groups' => [
            VideoStream::GROUP_READ_DEFAULT,
            VideoStream::GROUP_READ_COLLECTION,
            Tag::GROUP_READ_DEFAULT,
            UserGroup::GROUP_READ_DEFAULT,
        ],
    ];
    public const API_READ_DETAIL = [
        'swagger_definition_name' => 'Details',
        'groups' => [
            VideoStream::GROUP_READ_DEFAULT,
            VideoStream::GROUP_READ_COLLECTION,
            VideoStream::GROUP_READ_ITEM,
            Tag::GROUP_READ_DEFAULT,
            UserGroup::GROUP_READ_DEFAULT,
        ],
    ];
    public const API_UPDATE = [
        'swagger_definition_name' => 'Update',
        'groups' => [
            VideoStream::GROUP_UPDATE,
            Tag::GROUP_UPDATE_RELATION
        ],
    ];

    public const API_PATCH_DISABLED = [
        'swagger_definition_name' => 'Update disabled',
        'groups' => [
            VideoStream::GROUP_UPDATE_DISABLED
        ],
    ];

    public const API_PATCH_ENABLED = [
        'swagger_definition_name' => 'Update enabled',
        'groups' => [
            VideoStream::GROUP_UPDATE_ENABLED
        ],
    ];


    public const API_PATCH_NAME = [
        'swagger_definition_name' => 'Update name',
        'groups' => [
            VideoStream::GROUP_UPDATE_NAME,
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
     * @Groups({VideoStream::GROUP_READ_DEFAULT, VideoStream::GROUP_CREATE})
     * @Assert\Uuid
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_PARTIAL)
     * @Groups({VideoStream::GROUP_READ_DEFAULT, VideoStream::GROUP_UPDATE, VideoStream::GROUP_UPDATE_NAME})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     maxMessage="Maximum number of characters is 255",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=2500)
     * @Groups({VideoStream::GROUP_READ_DEFAULT, VideoStream::GROUP_UPDATE})
     * @Assert\Length(
     *     min=2,
     *     max=2500,
     *     maxMessage="Maximum number of characters is 2500",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $url;

    /**
     * @ORM\ManyToOne(targetEntity=UserGroup::class)
     * @ORM\JoinColumn(nullable=false)
     * @ApiFilter(SearchFilter::class, properties={
     *     "userGroup.name": SearchFilter::STRATEGY_PARTIAL
     * })
     * @Groups({VideoStream::GROUP_READ_COLLECTION, VideoStream::GROUP_UPDATE})
     */
    private $userGroup;

    /**
     * @var \DateTime Creation date
     *
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="create")
     * @Groups({VideoStream::GROUP_READ_DEFAULT})
     */
    private $createdAt;

    /**
     * @var \DateTime Last update date
     *
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="update")
     * @Groups({VideoStream::GROUP_READ_DEFAULT})
     */
    private $updatedAt;

    /**
     * @var bool Enable the video stream
     *
     * @ORM\Column(type="boolean")
     * @ApiFilter(BooleanFilter::class)
     * @Groups({VideoStream::GROUP_READ_DEFAULT, VideoStream::GROUP_UPDATE, VideoStream::GROUP_UPDATE_ENABLED, VideoStream::GROUP_UPDATE_DISABLED})
     */
    private $enabled = true;

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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = trim($url);

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
     * @return Collection
     */
    public function getUserGroups(): Collection
    {
        return new ArrayCollection([$this->getUserGroup()]);
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

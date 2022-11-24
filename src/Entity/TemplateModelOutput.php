<?php

namespace App\Entity;


use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Repository\TemplateModelOutputRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

/**
 * Template Model Output
 *
 * @ApiFilter(PropertyFilter::class)
 * @ApiResource(
 *   normalizationContext=TemplateModelOutput::API_READ,
 *   denormalizationContext=TemplateModelOutput::API_UPDATE,
 *     collectionOperations={
 *         "get"={"security"="is_granted('ROLE_TEMPLATE_MODEL_OUTPUT_GET_COLLECTION')"},
 *         "post"={"denormalization_context"=TemplateModelOutput::API_CREATE,
 *                 "security_post_denormalize" = "is_granted('ROLE_TEMPLATE_MODEL_OUTPUT_POST_COLLECTION', object)"}
 *     },
 *     itemOperations={
 *         "get"={"normalization_context"=TemplateModelOutput::API_READ_DETAIL,
 *                "security"="is_granted('ROLE_TEMPLATE_MODEL_OUTPUT_GET_ITEM', object)"},
 *         "delete"={"security"="is_granted('ROLE_TEMPLATE_MODEL_OUTPUT_DELETE_ITEM', object)"},
 *         "put"={"denormalization_context"=TemplateModelOutput::API_UPDATE,
 *                "security"="is_granted('ROLE_TEMPLATE_MODEL_OUTPUT_PUT_ITEM'; object)"},
 *         "patch"={"security"="is_granted('ROLE_TEMPLATE_MODEL_OUTPUT_PATCH_ITEM', object)"},
 *     }
 * )
 * @ORM\Entity(repositoryClass=TemplateModelOutputRepository::class)
 * @UniqueEntity("code")
 */
class TemplateModelOutput
{
    public const GROUP_CREATE = 'TemplateModelOutput:create';
    public const GROUP_READ_DEFAULT = 'TemplateModelOutput:read_default';
    public const GROUP_READ_COLLECTION = 'TemplateModelOutput:read_list';
    public const GROUP_READ_ITEM = 'TemplateModelOutput:read_detail';
    public const GROUP_UPDATE = 'TemplateModelOutput:write';

    public const API_CREATE = [
        'swagger_definition_name' => 'Create',
        'groups' => [
            TemplateModelOutput::GROUP_CREATE,
            TemplateModelOutput::GROUP_UPDATE,
        ],
    ];
    public const API_READ = [
        'swagger_definition_name' => 'Read',
        'groups' => [
            TemplateModelOutput::GROUP_READ_DEFAULT,
            TemplateModelOutput::GROUP_READ_COLLECTION,
            VideoStream::GROUP_READ_DEFAULT,
            VideoOverlay::GROUP_READ_DEFAULT,
            TemplateModel::GROUP_READ_DEFAULT
        ],
    ];
    public const API_READ_DETAIL = [
        'swagger_definition_name' => 'Detail',
        'groups' => [
            TemplateModelOutput::GROUP_READ_DEFAULT,
            TemplateModelOutput::GROUP_READ_COLLECTION,
            TemplateModelOutput::GROUP_READ_ITEM,
            VideoStream::GROUP_READ_DEFAULT,
            VideoOverlay::GROUP_READ_DEFAULT,
            TemplateModel::GROUP_READ_DEFAULT,
        ],
    ];
    public const API_UPDATE = [
        'swagger_definition_name' => 'Update',
        'groups' => [
            TemplateModelOutput::GROUP_UPDATE,
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
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_EXACT)
     * @ApiProperty(identifier=true)
     * @Groups({TemplateModelOutput::GROUP_READ_DEFAULT, TemplateModelOutput::GROUP_CREATE})
     */
    private $code;

    /**
     * @ORM\Column(type="integer")
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_EXACT)
     * @Groups({TemplateModelOutput::GROUP_READ_DEFAULT, TemplateModelOutput::GROUP_UPDATE})
     */
    private $number;

    /**
     * @ORM\ManyToOne(targetEntity=TemplateModel::class, inversedBy="templateModelOutputs")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({TemplateModelOutput::GROUP_READ_DEFAULT, TemplateModelOutput::GROUP_CREATE})
     */
    private $templateModel;

    /**
     * @ORM\ManyToOne(targetEntity=VideoStream::class)
     * @Groups({TemplateModelOutput::GROUP_READ_DEFAULT, TemplateModelOutput::GROUP_UPDATE})
     */
    private $videoStream;

    /**
     * @ORM\ManyToOne(targetEntity=VideoOverlay::class)
     * @Groups({TemplateModelOutput::GROUP_READ_DEFAULT, TemplateModelOutput::GROUP_UPDATE})
     */
    private $videoOverlay;

    /**
     * @var \DateTime Creation date
     *
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="create")
     * @Groups({TemplateModelOutput::GROUP_READ_DEFAULT})
     */
    private $createdAt;

    /**
     * @var \DateTime Last update date
     *
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="update")
     * @Groups({TemplateModelOutput::GROUP_READ_DEFAULT})
     */
    private $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getTemplateModel(): ?TemplateModel
    {
        return $this->templateModel;
    }

    public function setTemplateModel(?TemplateModel $templateModel): self
    {
        $this->templateModel = $templateModel;

        return $this;
    }

    public function getVideoStream(): ?VideoStream
    {
        return $this->videoStream;
    }

    public function setVideoStream(?VideoStream $videoStream): self
    {
        $this->videoStream = $videoStream;

        return $this;
    }

    public function getVideoOverlay(): ?VideoOverlay
    {
        return $this->videoOverlay;
    }

    public function setVideoOverlay(?VideoOverlay $videoOverlay): self
    {
        $this->videoOverlay = $videoOverlay;

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
     * @return Uuid
     */
    public function getCode(): Uuid
    {
        return $this->code;
    }

    /**
     * @param Uuid $code
     */
    public function setCode(Uuid $code): void
    {
        $this->code = $code;
    }

    /**
     * @Groups({TemplateModelOutput::GROUP_READ_DEFAULT})
     * @return string
     */
    public function getName(): string
    {
        return $this->getTemplateModel()->getName() . " - " . $this->getNumber();
    }
}

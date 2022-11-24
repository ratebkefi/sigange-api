<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Interfaces\UserGroupVisibilityInterface;
use App\Repository\TemplateModelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Template model of site devices configuration
 * @ApiFilter(PropertyFilter::class)
 * @ApiFilter (OrderFilter::class, properties={
 *     "code",
 *     "name",
 *     "platform.name",
 *     "userGroup.name",
 *     "createdAt",
 *     "updatedAt"
 *     })
 * @ApiResource(
 *     normalizationContext=TemplateModel::API_READ,
 *     denormalizationContext=TemplateModel::API_UPDATE,
 *     collectionOperations={
 *         "get"={"security"="is_granted('ROLE_TEMPLATE_MODEL_GET_COLLECTION')"},
 *         "post"={"denormalization_context"=TemplateModel::API_CREATE,
 *                 "security_post_denormalize" = "is_granted('ROLE_TEMPLATE_MODEL_POST_COLLECTION', object)"}
 *     },
 *     itemOperations={
 *         "get"={"normalization_context"=TemplateModel::API_READ_DETAIL, "security"="is_granted('ROLE_TEMPLATE_MODEL_GET_ITEM', object)"},
 *         "delete"={"security"="is_granted('ROLE_TEMPLATE_MODEL_DELETE_ITEM', object)"},
 *         "put"={"denormalization_context"=TemplateModel::API_UPDATE, "security"="is_granted('ROLE_TEMPLATE_MODEL_PUT_ITEM', object)"},
 *         "patch"={"security"="is_granted('ROLE_TEMPLATE_MODEL_PATCH_ITEM', object)"},
 *     }
 * )
 * @ORM\Entity(repositoryClass=TemplateModelRepository::class)
 * @UniqueEntity("code")
 */
class TemplateModel implements UserGroupVisibilityInterface
{

    public const GROUP_CREATE = 'TemplateModel:create';
    public const GROUP_READ_DEFAULT = 'TemplateModel:read_default';
    public const GROUP_READ_COLLECTION = 'TemplateModel:read_list';
    public const GROUP_READ_ITEM = 'TemplateModel:read_detail';
    public const GROUP_UPDATE = 'TemplateModel:write';

    public const API_CREATE = [
        'swagger_definition_name' => 'Create',
        'groups' => [
            TemplateModel::GROUP_CREATE,
            TemplateModel::GROUP_UPDATE,
            TemplateModelOutput::GROUP_CREATE,
            TemplateModelOutput::GROUP_UPDATE,
        ],
    ];
    public const API_READ = [
        'swagger_definition_name' => 'Read',
        'groups' => [
            TemplateModel::GROUP_READ_DEFAULT,
            TemplateModel::GROUP_READ_COLLECTION,
            TemplateModelOutput::GROUP_READ_DEFAULT,
            Platform::GROUP_READ_DEFAULT,
            VideoStream::GROUP_READ_DEFAULT,
            VideoOverlay::GROUP_READ_DEFAULT,
            UserGroup::GROUP_READ_DEFAULT
        ],
    ];
    public const API_READ_DETAIL = [
        'swagger_definition_name' => 'Detail',
        'groups' => [
            TemplateModel::GROUP_READ_DEFAULT,
            TemplateModel::GROUP_READ_COLLECTION,
            TemplateModel::GROUP_READ_ITEM,
            TemplateModelOutput::GROUP_READ_DEFAULT,
            Platform::GROUP_READ_DEFAULT,
            VideoStream::GROUP_READ_DEFAULT,
            VideoOverlay::GROUP_READ_DEFAULT,
        ],
    ];
    public const API_UPDATE = [
        'swagger_definition_name' => 'Update',
        'groups' => [
            TemplateModel::GROUP_UPDATE,
            TemplateModelOutput::GROUP_CREATE,
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
     * @ApiProperty(identifier=true)
     * @Groups({TemplateModel::GROUP_READ_DEFAULT, TemplateModel::GROUP_CREATE})
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     * @ApiFilter(SearchFilter::class, strategy=SearchFilter::STRATEGY_PARTIAL)
     * @Groups({TemplateModel::GROUP_READ_DEFAULT, TemplateModel::GROUP_UPDATE})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     maxMessage="Maximum number of characters is 255",
     *     minMessage="Minimum number of characters is 2"
     * )
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Platform::class, inversedBy="templateModels")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({TemplateModel::GROUP_READ_DEFAULT, TemplateModel::GROUP_UPDATE})
     */
    private $platform;


    /**
     * @var Collection|TemplateModelOutput[] List of template model outputs
     * @ORM\OneToMany(targetEntity=TemplateModelOutput::class, mappedBy="templateModel", orphanRemoval=true, cascade={"persist"})
     * @Groups({TemplateModel::GROUP_READ_DEFAULT, TemplateModel::GROUP_UPDATE})
     */
    private $templateModelOutputs;

    /**
     * @ORM\ManyToOne(targetEntity=UserGroup::class)
     * @ORM\JoinColumn(nullable=true)
     * @Groups({TemplateModel::GROUP_READ_ITEM, TemplateModel::GROUP_READ_DEFAULT, TemplateModel::GROUP_UPDATE})
     * @ApiFilter(SearchFilter::class, properties={
     *     "userGroup.name": SearchFilter::STRATEGY_PARTIAL,
     *     "userGroup.customer.name": SearchFilter::STRATEGY_PARTIAL,
     * })
     */
    private $userGroup;

    /**
     * @var \DateTime Creation date
     *
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="create")
     * @Groups({TemplateModel::GROUP_READ_DEFAULT})
     */
    private $createdAt;

    /**
     * @var \DateTime Last update date
     *
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="update")
     * @Groups({TemplateModel::GROUP_READ_DEFAULT})
     */
    private $updatedAt;

    public function __construct()
    {
        $this->templateModelOutputs = new ArrayCollection();
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

    public function getPlatform(): ?Platform
    {
        return $this->platform;
    }

    public function setPlatform(?Platform $platform): self
    {
        $this->platform = $platform;

        return $this;
    }

    /**
     * @return Collection|TemplateModelOutput[]
     */
    public function getTemplateModelOutputs(): Collection
    {
        return $this->templateModelOutputs;
    }

    public function addTemplateModelOutput(TemplateModelOutput $templateModelOutput): self
    {
        if (!$this->templateModelOutputs->contains($templateModelOutput)) {
            $this->templateModelOutputs[] = $templateModelOutput;
            $templateModelOutput->setTemplateModel($this);
        }

        return $this;
    }

    public function removeTemplateModelOutput(TemplateModelOutput $templateModelOutput): self
    {
        if ($this->templateModelOutputs->removeElement($templateModelOutput)) {
            // set the owning side to null (unless already changed)
            if ($templateModelOutput->getTemplateModel() === $this) {
                $templateModelOutput->setTemplateModel(null);
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
     * @return UserGroup|null
     */
    public function getUserGroup(): ?UserGroup
    {
        return $this->userGroup;
    }

    /**
     * @param UserGroup|null $userGroup
     * @return $this
     */
    public function setUserGroup(?UserGroup $userGroup): self
    {
        $this->userGroup = $userGroup;

        return $this;
    }


    /**
     * @return Collection
     */
    public function getUserGroups(): Collection
    {
        return new ArrayCollection([$this->getUserGroup()]);
    }
}

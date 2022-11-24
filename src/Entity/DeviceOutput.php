<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Controller\DeviceOutputCustomController;
use App\Filter\OrSearchFilter;
use App\Interfaces\UserGroupVisibilityInterface;
use App\Repository\DeviceOutputRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

/**
 * Device output
 *
 * @ApiFilter(PropertyFilter::class)
 * @ApiFilter(OrSearchFilter::class, properties={
 *             "search_global"={
 *                 "device.name": SearchFilter::STRATEGY_PARTIAL,
 *                 "videoStream.name": SearchFilter::STRATEGY_PARTIAL,
 *                 "videoOverlay.name": SearchFilter::STRATEGY_PARTIAL,
 *                 "screen.name": SearchFilter::STRATEGY_PARTIAL,
 *                 "screen.code": SearchFilter::STRATEGY_EXACT
 *             }
 *           })
 * @ApiFilter (OrderFilter::class, properties={
 *     "code",
 *     "modelOutput.name",
 *     "device.macAddress",
 *     "device.serialNumber",
 *     "device.model.name",
 *     "device.status.name",
 *     "device.userGroup.customer.name",
 *     "device.userGroup.name",
 *     "device.site.name",
 *     "device.site.externalId",
 *     "device.network.name",
 *     "device.platform.name",
 *     "videoStream.name",
 *     "videoOverlay.name",
 *     "screen.name",
 *     "templateModelOutput.name",
 *     "createdAt",
 *     "updatedAt"
 *     })
 * @ApiResource(
 *     normalizationContext=DeviceOutput::API_READ,
 *     denormalizationContext=DeviceOutput::API_UPDATE,
 *     collectionOperations={
 *         "get"={"security"="is_granted('ROLE_DEVICE_OUTPUT_GET_COLLECTION')"},
 *         "post"={"denormalization_context"=DeviceOutput::API_CREATE,
 *                 "security_post_denormalize" = "is_granted('ROLE_DEVICE_OUTPUT_POST_COLLECTION', object)"}
 *     },
 *     itemOperations={
 *         "get"={"normalization_context"=DeviceOutput::API_READ_DETAIL, "security"="is_granted('ROLE_DEVICE_OUTPUT_GET_ITEM', object)"},
 *         "delete"={"security"="is_granted('ROLE_DEVICE_OUTPUT_DELETE_ITEM', object)"},
 *         "put"={"denormalization_context"=DeviceOutput::API_UPDATE, "security"="is_granted('ROLE_DEVICE_OUTPUT_PUT_ITEM', object)"},
 *         "patch"={"security"="is_granted('ROLE_DEVICE_OUTPUT_PATCH_ITEM', object)"},
 *         "patch_video_stream" = {
 *             "method"="PATCH",
 *             "path"="/device_outputs/{code}/video_stream",
 *             "security"="is_granted('ROLE_DEVICE_OUTPUT_PATCH_VIDEO_STREAM')",
 *             "denormalization_context"= DeviceOutput::API_PATCH_VIDEO_STREAM,
 *             "normalization_context"= DeviceOutput::API_PATCH_VIDEO_STREAM,
 *             "openapi_context" = {
 *                 "summary" = "Set the DeviceOutput video stream",
 *                 "description" = "Updates only the **video stream** of a Device Output"
 *             }
 *         },
 *         "patch_video_overlay" = {
 *             "method"="PATCH",
 *             "path"="/device_outputs/{code}/video_overlay",
 *             "security"="is_granted('ROLE_DEVICE_OUTPUT_PATCH_VIDEO_OVERLAY')",
 *             "denormalization_context"= DeviceOutput::API_PATCH_VIDEO_OVERLAY,
 *             "normalization_context"= DeviceOutput::API_PATCH_VIDEO_OVERLAY,
 *             "openapi_context" = {
 *                 "summary" = "Set the DeviceOutput video overlay",
 *                 "description" = "Updates only the **video overlay** of a Device Output"
 *             }
 *         },
 *         "api_device_outputs_patch_enable" = {
 *             "route_name"="api_device_outputs_patch_enable",
 *             "controller"=DeviceOutputCustomController::class,
 *             "method"="PATCH",
 *             "path"="/device_outputs/{code}/enable",
 *             "denormalization_context"= DeviceOutput::API_PATCH_ENABLED,
 *             "normalization_context"= DeviceOutput::API_PATCH_ENABLED,
 *             "openapi_context" = {
 *                 "summary" = "Disable the DeviceOutput",
 *                 "description" = "Set the Device Output as `enabled: true`"
 *             }
 *         },
 *         "api_device_outputs_patch_disable" = {
 *             "route_name"="api_device_outputs_patch_disable",
 *             "controller"=DeviceOutputCustomController::class,
 *             "method"="PATCH",
 *             "path"="/device_outputs/{code}/disable",
 *             "denormalization_context"= DeviceOutput::API_PATCH_DISABLED,
 *             "normalization_context"= DeviceOutput::API_PATCH_DISABLED,
 *             "openapi_context" = {
 *                 "summary" = "Enable the DeviceOutput",
 *                 "description" = "Set the Device Output as `enabled: false`"
 *             }
 *         },
 *     }
 * )
 * @ORM\Entity(repositoryClass=DeviceOutputRepository::class)
 * @UniqueEntity("code")
 */
class DeviceOutput implements UserGroupVisibilityInterface
{
    public const GROUP_CREATE = 'device_output:create';
    public const GROUP_READ_DEFAULT = 'device_output:read_default';
    public const GROUP_READ_COLLECTION = 'device_output:read_list';
    public const GROUP_READ_ITEM = 'device_output:read_detail';
    public const GROUP_UPDATE = 'device_output:write';
    public const GROUP_UPDATE_ENABLED = 'device_output:write_enabled';
    public const GROUP_UPDATE_DISABLED = 'device_output:write_disabled';
    public const GROUP_UPDATE_VIDEO_STREAM = 'device:write_video_stream';
    public const GROUP_UPDATE_VIDEO_OVERLAY = 'device:write_video_overlay';

    public const API_CREATE = [
        'swagger_definition_name' => 'Create',
        'groups' => [
            DeviceOutput::GROUP_CREATE,
            DeviceOutput::GROUP_UPDATE,
        ],
    ];
    public const API_READ = [
        'swagger_definition_name' => 'Read',
        'groups' => [
            Device::GROUP_READ_COLLECTION,
            Device::GROUP_READ_DEFAULT,
            DeviceOutput::GROUP_READ_DEFAULT,
            DeviceDiagnostic::GROUP_READ_DEFAULT,
            UserGroup::GROUP_READ_DEFAULT,
            Site::GROUP_READ_DEFAULT,
            DeviceOutput::GROUP_READ_COLLECTION,
            VideoOverlay::GROUP_READ_DEFAULT,
            VideoStream::GROUP_READ_DEFAULT,
            Tag::GROUP_READ_DEFAULT,
            DeviceModelOutput::GROUP_READ_DEFAULT,
        ],
    ];
    public const API_READ_DETAIL = [
        'swagger_definition_name' => 'Detail',
        'groups' => [
            Device::GROUP_READ_COLLECTION,
            Device::GROUP_READ_DEFAULT,
            Device::GROUP_READ_ITEM,
            Site::GROUP_READ_DEFAULT,
            UserGroup::GROUP_READ_DEFAULT,
            DeviceOutput::GROUP_READ_DEFAULT,
            DeviceOutput::GROUP_READ_COLLECTION,
            DeviceOutput::GROUP_READ_ITEM,
            VideoOverlay::GROUP_READ_DEFAULT,
            VideoOverlay::GROUP_READ_COLLECTION,
            VideoStream::GROUP_READ_DEFAULT,
            VideoStream::GROUP_READ_COLLECTION,
            Tag::GROUP_READ_DEFAULT,
            TagGroup::GROUP_READ_DEFAULT,
            DeviceModelOutput::GROUP_READ_DEFAULT,
            DeviceDiagnostic::GROUP_READ_DEFAULT,
        ],
    ];
    public const API_UPDATE = [
        'swagger_definition_name' => 'Update',
        'groups' => [
            DeviceOutput::GROUP_UPDATE,
        ],
    ];

    public const API_PATCH_DISABLED = [
        'swagger_definition_name' => 'Set the Device Output as enabled false',
        'groups' => [
            DeviceOutput::GROUP_UPDATE_DISABLED
        ],
    ];

    public const API_PATCH_ENABLED = [
        'swagger_definition_name' => 'Set the Device Output as enabled true',
        'groups' => [
            DeviceOutput::GROUP_UPDATE_ENABLED
        ],
    ];

    public const API_PATCH_VIDEO_STREAM = [
        'swagger_definition_name' => 'Update video stream',
        "skip_null_values" => false,
        'groups' => [
            DeviceOutput::GROUP_UPDATE_VIDEO_STREAM,
            VideoStream::GROUP_READ_DEFAULT,
        ],
    ];

    public const API_PATCH_VIDEO_OVERLAY = [
        'swagger_definition_name' => 'Update video overlay',
        "skip_null_values" => false,
        'groups' => [
            DeviceOutput::GROUP_UPDATE_VIDEO_OVERLAY,
            VideoOverlay::GROUP_READ_DEFAULT,
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
     * @var Uuid External identifier used by API
     *
     * @ORM\Column(type="uuid", unique=true)
     * @ApiProperty(identifier=true)
     * @Groups({DeviceOutput::GROUP_READ_DEFAULT, DeviceOutput::GROUP_CREATE})
     * @ApiFilter(SearchFilter::class, properties={
     *     "code": SearchFilter::STRATEGY_PARTIAL
     * })
     */
    private $code;

    /**
     * @var bool Enable the device output
     *
     * @ORM\Column(type="boolean")
     * @ApiFilter(BooleanFilter::class)
     * @Groups({DeviceOutput::GROUP_READ_DEFAULT, DeviceOutput::GROUP_UPDATE, DeviceOutput::GROUP_UPDATE_ENABLED, DeviceOutput::GROUP_UPDATE_DISABLED})
     */
    private $enabled = true;

    /**
     * @var Device Device
     * @ORM\ManyToOne(targetEntity=Device::class, inversedBy="outputs")
     * @ORM\JoinColumn(nullable=false)
     * @ApiFilter(BooleanFilter::class, properties={
     *      "device.enabled"
     * })
     * @ApiFilter(SearchFilter::class, properties={
     *     "device.macAddress": SearchFilter::STRATEGY_EXACT,
     *     "device.serialNumber": SearchFilter::STRATEGY_EXACT,
     *     "device.name": SearchFilter::STRATEGY_PARTIAL,
     *     "device.site.externalId": SearchFilter::STRATEGY_EXACT,
     *     "device.site.name": SearchFilter::STRATEGY_EXACT,
     *     "device.tags.name": SearchFilter::STRATEGY_PARTIAL,
     *     "device.status.name": SearchFilter::STRATEGY_EXACT,
     *     "device.model.name": SearchFilter::STRATEGY_EXACT,
     *     "device.userGroup.customer.name": SearchFilter::STRATEGY_EXACT,
     *     "device.userGroup.name": SearchFilter::STRATEGY_EXACT,
     *     "device.network.name": SearchFilter::STRATEGY_EXACT,
     *     "device.platform.name": SearchFilter::STRATEGY_EXACT,
     * })
     * @ApiFilter(ExistsFilter::class, properties={
     *     "device.site"
     *})
     * @Groups({DeviceOutput::GROUP_READ_DEFAULT, DeviceOutput::GROUP_CREATE})
     */
    private $device;

    /**
     * @var DeviceModelOutput Model output
     *
     * @ORM\ManyToOne(targetEntity=DeviceModelOutput::class)
     * @ORM\JoinColumn(nullable=false)
     * @ApiFilter(SearchFilter::class, properties={
     *     "modelOutput.name": SearchFilter::STRATEGY_PARTIAL
     * })
     * @Groups({DeviceOutput::GROUP_READ_DEFAULT, DeviceOutput::GROUP_CREATE})
     */
    private $modelOutput;

    /**
     * @var VideoStream Video stream of the device output
     * @ORM\ManyToOne(targetEntity=VideoStream::class)
     * @Groups({DeviceOutput::GROUP_READ_DEFAULT, DeviceOutput::GROUP_UPDATE, DeviceOutput::GROUP_UPDATE_VIDEO_STREAM})
     * @ApiFilter(ExistsFilter::class, properties={
     *     "videoStream"
     *})
     * @ApiFilter(SearchFilter::class, properties={
     *     "videoStream.name": SearchFilter::STRATEGY_PARTIAL
     * })
     */
    private $videoStream;

    /**
     * @var VideoOverlay Video overlay of the the device output (data displayed over the video stream)
     * @ORM\ManyToOne(targetEntity=VideoOverlay::class)
     * @Groups({DeviceOutput::GROUP_READ_DEFAULT, DeviceOutput::GROUP_UPDATE, DeviceOutput::GROUP_UPDATE_VIDEO_OVERLAY})
     * @ApiFilter(SearchFilter::class, properties={
     *     "videoOverlay.name": SearchFilter::STRATEGY_PARTIAL
     * })
     */
    private $videoOverlay;

    /**
     * @var Screen Screen linked to the device output
     *
     * @ORM\OneToOne(targetEntity=Screen::class, mappedBy="deviceOutput", cascade={"persist", "remove"})
     * @Groups({DeviceOutput::GROUP_READ_DEFAULT, DeviceOutput::GROUP_UPDATE})
     * @ApiFilter(SearchFilter::class, properties={
     *     "screen.name": SearchFilter::STRATEGY_PARTIAL
     * })
     */
    private $screen;

    /**
     * @var TemplateModelOutput Output of the template model used
     * @ORM\ManyToOne(targetEntity=TemplateModelOutput::class)
     * @Groups({DeviceOutput::GROUP_READ_ITEM,DeviceOutput::GROUP_READ_DEFAULT, DeviceOutput::GROUP_UPDATE})
     * @ApiFilter(SearchFilter::class, properties={
     *     "templateModelOutput.name": SearchFilter::STRATEGY_PARTIAL
     * })
     */
    private $templateModelOutput;

    /**
     * @var \DateTime Creation date
     *
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="create")
     * @Groups({DeviceOutput::GROUP_READ_DEFAULT})
     */
    private $createdAt;

    /**
     * @var \DateTime Last update date
     *
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="update")
     * @Groups({DeviceOutput::GROUP_READ_DEFAULT})
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

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getDevice(): ?Device
    {
        return $this->device;
    }

    public function setDevice(?Device $device): self
    {
        $this->device = $device;

        return $this;
    }

    public function getModelOutput(): ?DeviceModelOutput
    {
        return $this->modelOutput;
    }

    public function setModelOutput(?DeviceModelOutput $modelOutput): self
    {
        $this->modelOutput = $modelOutput;

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

    public function getScreen(): ?Screen
    {
        return $this->screen;
    }

    public function setScreen(?Screen $screen): self
    {
        // unset previous owning side
        if ($this->screen !== null) {
            $this->screen->setDeviceOutput(null); // old screen need to be persisted ?
        }

        // if screen not null set owing side
        if ($screen !== null) {
            $screen->setDeviceOutput($this);
        }
        $this->screen = $screen;
        return $this;
    }

    public function getTemplateModelOutput(): ?TemplateModelOutput
    {
        return $this->templateModelOutput;
    }

    public function setTemplateModelOutput(?TemplateModelOutput $templateModelOutput): self
    {
        $this->templateModelOutput = $templateModelOutput;

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
     * @Groups({DeviceOutput::GROUP_READ_DEFAULT})
     * @return string
     */
    public function getVideoOverlayUrl(): string
    {
        if ($this->getVideoOverlay() !== null) {
            if ($this->getDevice()->getSite() !== null) {
                return str_replace('%SITE_EXTERNAL_ID%', $this->getDevice()->getSite()->getExternalId(),
                    $this->getVideoOverlay()->getUrl());
            }
            return $this->getVideoOverlay()->getUrl();
        }

        return '';
    }

    /**
     * @Groups({DeviceOutput::GROUP_READ_DEFAULT})
     * @return string
     */
    public function getVideoStreamUrl(): string
    {
        if ($this->getVideoStream() !== null) {
            if ($this->getDevice()->getSite() !== null) {
                return str_replace('%SITE_EXTERNAL_ID%', $this->getDevice()->getSite()->getExternalId(),
                    $this->getVideoStream()->getUrl());
            }
            return $this->getVideoStream()->getUrl();
        }

        return '';
    }

    /**
     * Return true if Device, DeviceOutput and VideoOverlay or VideoStream are enabled, false in the other cases.
     * We must handle the case where only the VideoOverlay or the VideoStream is enabled.
     * @Groups({DeviceOutput::GROUP_READ_DEFAULT})
     * @return bool
     */
    public function isFullyEnabled(): bool
    {
        $enabledDevice = $this->getDevice() && $this->getDevice()->isEnabled() === true;
        $enabledOverlay = $this->getVideoOverlay() && $this->getVideoOverlay()->isEnabled() === true;
        $enabledStream = $this->getVideoStream() && $this->getVideoStream()->isEnabled() === true;

        return $enabledDevice &&
            $this->isEnabled() === true &&
            ($enabledOverlay ||
            $enabledStream);
    }

    public function getUserGroups(): Collection
    {
        return $this->getDevice()->getUserGroups();
    }

    /**
     * @Groups({DeviceOutput::GROUP_READ_DEFAULT})
     * @return string
     */
    public function getName(): string
    {
        $deviceName = $this->getDevice()->getName() === $this->getDevice()->getMacAddress() ?
            $this->getDevice()->getName() :
            $this->getDevice()->getName() . " - " . $this->getDevice()->getMacAddress();
        return $deviceName . " - " . $this->getModelOutput()->getName() . " - " . $this->getModelOutput()->getNumber();
    }

}

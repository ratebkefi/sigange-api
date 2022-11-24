<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;

use App\Repository\DeviceDiagnosticRepository;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Device diagnostic data
 * @ApiFilter(PropertyFilter::class)
 * @ApiResource(
 *     normalizationContext=DeviceDiagnostic::API_READ,
 *     denormalizationContext=DeviceDiagnostic::API_UPDATE,
 *     collectionOperations={
 *         "get"={"security"="is_granted('ROLE_DEVICE_DIAGNOSTIC_GET_COLLECTION')"},
 *         "post"={"denormalization_context"=DeviceDiagnostic::API_CREATE,
 *                 "security_post_denormalize" = "is_granted('ROLE_DEVICE_DIAGNOSTIC_POST_COLLECTION', object)"}
 *     },
 *     itemOperations={
 *         "get"={"normalization_context"=DeviceDiagnostic::API_READ_DETAIL, "security"="is_granted('ROLE_DEVICE_DIAGNOSTIC_GET_ITEM', object)"},
 *         "delete"={ "security"="is_granted('ROLE_DEVICE_DIAGNOSTIC_DELETE_ITEM', object)"},
 *         "put"={"denormalization_context"=DeviceDiagnostic::API_UPDATE, "security"="is_granted('ROLE_DEVICE_DIAGNOSTIC_PUT_ITEM', object)"},
 *         "patch"={"security"="is_granted('ROLE_DEVICE_DIAGNOSTIC_PATCH_COLLECTION', object)"}
 *     }
 * )
 * @ORM\Entity(repositoryClass=DeviceDiagnosticRepository::class)
 * @UniqueEntity("code")
 */
class DeviceDiagnostic
{
    public const GROUP_CREATE = 'device_diagnostic:create';
    public const GROUP_READ_DEFAULT = 'device_diagnostic:read_default';
    public const GROUP_READ_COLLECTION = 'device_diagnostic:read_list';
    public const GROUP_READ_ITEM = 'device_diagnostic:read_detail';
    public const GROUP_UPDATE = 'device_diagnostic:write';

    public const API_CREATE = [
        'swagger_definition_name' => 'Create',
        'groups' => [
            DeviceDiagnostic::GROUP_CREATE,
            DeviceDiagnostic::GROUP_UPDATE,
        ],
    ];
    public const API_READ = [
        'swagger_definition_name' => 'Read',
        'groups' => [
            DeviceDiagnostic::GROUP_READ_DEFAULT,
            DeviceDiagnostic::GROUP_READ_COLLECTION,
        ],
    ];
    public const API_READ_DETAIL = [
        'swagger_definition_name' => 'Detail',
        'groups' => [
            DeviceDiagnostic::GROUP_READ_DEFAULT,
            DeviceDiagnostic::GROUP_READ_COLLECTION,
            DeviceDiagnostic::GROUP_READ_ITEM,
        ],
    ];
    public const API_UPDATE = [
        'swagger_definition_name' => 'Update',
        'groups' => [
            DeviceDiagnostic::GROUP_UPDATE,
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
     * @Groups({DeviceDiagnostic::GROUP_READ_DEFAULT, DeviceDiagnostic::GROUP_CREATE})
     */
    private $code;

    /**
     * @var Device Related device
     *
     * @ORM\OneToOne(targetEntity=Device::class, inversedBy="diagnostic", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @ApiFilter(SearchFilter::class, properties={
     *     "device.macAddress": SearchFilter::STRATEGY_EXACT,
     *     "device.name": SearchFilter::STRATEGY_PARTIAL
     * })
     * @Groups({DeviceDiagnostic::GROUP_READ_DEFAULT, DeviceDiagnostic::GROUP_CREATE})
     */
    private $device;

    /**
     * @var string Device IPv4 address
     *
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Groups({DeviceDiagnostic::GROUP_READ_DEFAULT, DeviceDiagnostic::GROUP_UPDATE})
     * @Assert\Length(
     *     min=7,
     *     max=15,
     *     maxMessage="Maximum number of characters is 15",
     *     minMessage="Minimum number of characters is 7"
     * )
     */
    private $ipV4;

    /**
     * @var string Device IPv6 address
     *
     * @ORM\Column(type="string", length=46, nullable=true)
     * @Groups({DeviceDiagnostic::GROUP_READ_DEFAULT, DeviceDiagnostic::GROUP_UPDATE})
     * @Assert\Length(
     *     min=12,
     *     max=46,
     *     maxMessage="Maximum number of characters is 46",
     *     minMessage="Minimum number of characters is 12"
     * )
     */
    private $ipV6;

    /**
     * @var \DateTime Last succeed device ping date
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @ApiFilter(DateFilter::class)
     * @Groups({DeviceDiagnostic::GROUP_READ_DEFAULT, DeviceDiagnostic::GROUP_UPDATE})
     */
    private $lastPingAt;

    /**
     * @var \DateTime Last succeed device HTTP connection date
     *
     * @ORM\Column(type="datetime",nullable=true)
     * @ApiFilter(DateFilter::class)
     * @Groups({DeviceDiagnostic::GROUP_READ_DEFAULT, DeviceDiagnostic::GROUP_UPDATE})
     */
    private $lastHttpConnectionAt;

    /**
     * @var \DateTime Last succeed device SSH connection date
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @ApiFilter(DateFilter::class)
     * @Groups({DeviceDiagnostic::GROUP_READ_DEFAULT, DeviceDiagnostic::GROUP_UPDATE})
     */
    private $lastSshConnectionAt;

    /**
     * @var \DateTime Last succeed device VNP connection date
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @ApiFilter(DateFilter::class)
     * @Groups({DeviceDiagnostic::GROUP_READ_DEFAULT, DeviceDiagnostic::GROUP_UPDATE})
     */
    private $lastVpnConnectionAt;

    /**
     * @var \DateTime Last succeed device websocket connection date
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @ApiFilter(DateFilter::class)
     * @Groups({DeviceDiagnostic::GROUP_READ_DEFAULT, DeviceDiagnostic::GROUP_UPDATE})
     */
    private $lastWebsocketConnectionAt;

    /**
     * @var \DateTime Creation date
     *
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="create")
     * @Groups({DeviceDiagnostic::GROUP_READ_DEFAULT})
     */
    private $createdAt;

    /**
     * @var \DateTime Last update date
     *
     * @ORM\Column(type="datetime")
     * @ApiFilter(DateFilter::class)
     * @Gedmo\Timestampable(on="update")
     * @Groups({DeviceDiagnostic::GROUP_READ_DEFAULT})
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

    public function getDevice(): ?Device
    {
        return $this->device;
    }

    public function setDevice(Device $device): self
    {
        $this->device = $device;

        return $this;
    }

    public function getIpV4(): ?string
    {
        return $this->ipV4;
    }

    public function setIpV4(string $ipV4): self
    {
        $this->ipV4 = $ipV4;

        return $this;
    }

    public function getIpV6(): ?string
    {
        return $this->ipV6;
    }

    public function setIpV6(?string $ipV6): self
    {
        $this->ipV6 = $ipV6;

        return $this;
    }

    public function getLastPingAt(): ?\DateTimeInterface
    {
        return $this->lastPingAt;
    }

    public function setLastPingAt(?\DateTimeInterface $lastPingAt): self
    {
        $this->lastPingAt = $lastPingAt;

        return $this;
    }

    public function getLastHttpConnectionAt(): ?\DateTimeInterface
    {
        return $this->lastHttpConnectionAt;
    }

    public function setLastHttpConnectionAt(\DateTimeInterface $lastHttpConnectionAt): self
    {
        $this->lastHttpConnectionAt = $lastHttpConnectionAt;

        return $this;
    }

    public function getLastSshConnectionAt(): ?\DateTimeInterface
    {
        return $this->lastSshConnectionAt;
    }

    public function setLastSshConnectionAt(?\DateTimeInterface $lastSshConnectionAt): self
    {
        $this->lastSshConnectionAt = $lastSshConnectionAt;

        return $this;
    }

    public function getLastVpnConnectionAt(): ?\DateTimeInterface
    {
        return $this->lastVpnConnectionAt;
    }

    public function setLastVpnConnectionAt(?\DateTimeInterface $lastVpnConnectionAt): self
    {
        $this->lastVpnConnectionAt = $lastVpnConnectionAt;

        return $this;
    }

    public function getLastWebsocketConnectionAt(): ?\DateTimeInterface
    {
        return $this->lastWebsocketConnectionAt;
    }

    public function setLastWebsocketConnectionAt(?\DateTimeInterface $lastWebsocketConnectionAt): self
    {
        $this->lastWebsocketConnectionAt = $lastWebsocketConnectionAt;

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


}

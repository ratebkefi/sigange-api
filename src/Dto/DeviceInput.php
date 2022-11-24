<?php

namespace App\Dto;

use App\Entity\Device;
use App\Entity\DeviceDiagnostic;
use App\Entity\DeviceModel;
use App\Entity\DeviceOutput;
use App\Entity\DeviceStatus;
use App\Entity\Network;
use App\Entity\Platform;
use App\Entity\Site;
use App\Entity\Tag;
use App\Entity\UserGroup;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Uid\Uuid;

final class DeviceInput
{

    /**
     * An array of arrays where each first dimension index is the group tag name and the second dimensions arrays are
     * the corresponding tags.
     * tags
     * @var ?array<object, array<Tag>>
     * @Groups({Device::GROUP_READ_DEFAULT, Device::GROUP_UPDATE})
     * @SerializedName("tags")
     */
    public array $tagsData = [];

    /**
     * @var Uuid External identifier used by API
     *
     * @Groups({Device::GROUP_READ_DEFAULT, Device::GROUP_CREATE})
     */
    public $code;

    /**
     * @var string Name of the device
     *
     * @Groups({Device::GROUP_READ_DEFAULT, Device::GROUP_UPDATE, Device::GROUP_UPDATE_NAME})
     */
    public $name;

    /**
     * @var ?string Description of the device
     *
     * @Groups({Device::GROUP_READ_DEFAULT, Device::GROUP_UPDATE, Device::GROUP_UPDATE_NAME})
     */
    public $description;

    /**
     * @Groups({Device::GROUP_READ_ITEM, Device::GROUP_READ_DEFAULT,Device::GROUP_UPDATE})
     * @var ?UserGroup
     */
    public $userGroup;

    /**
     * @var bool Enable the device
     *
     * @Groups({Device::GROUP_READ_DEFAULT, Device::GROUP_UPDATE, Device::GROUP_UPDATE_ENABLED, Device::GROUP_UPDATE_ENABLED, Device::GROUP_READ_ITEM})
     */
    public $enabled = true;

    /**
     * @var DeviceModel Model of the device (not updatable after creation)
     *
     * @Groups({Device::GROUP_READ_DEFAULT, Device::GROUP_CREATE})
     */
    public $model;

    /**
     * @var ?Site Location of the device
     * @Groups({Device::GROUP_READ_COLLECTION, Device::GROUP_UPDATE, Device::GROUP_UPDATE_SITE})
     */
    public $site;

    /**
     * @var ?Network Network
     * @Groups({Device::GROUP_READ_COLLECTION, Device::GROUP_UPDATE, Device::GROUP_UPDATE_NETWORK})
     */
    public $network;

    /**
     * @var ?Platform Platform
     *
     * @Groups({Device::GROUP_READ_COLLECTION, Device::GROUP_UPDATE, Device::GROUP_UPDATE_PLATFORM})
     */
    public $platform;

    /**
     * @var ?string Comment about the device
     *
     * @Groups({Device::GROUP_READ_ITEM, Device::GROUP_UPDATE, Device::GROUP_UPDATE_COMMENT})
     */
    public $comment;

    /**
     * @var ?string Comment about the device (only for SUPER_ADMIN)
     *
     * @Groups({Device::GROUP_READ_ITEM, Device::GROUP_UPDATE, Device::GROUP_UPDATE_INTERNAL_COMMENT})
     */
    public $internalComment;

    /**
     * @var ?string Serial number of the device
     *
     * @Groups({Device::GROUP_READ_COLLECTION, Device::GROUP_UPDATE})
     */
    public $serialNumber;

    /**
     * @var string MAC address of the device
     *
     * @Groups({Device::GROUP_READ_DEFAULT, Device::GROUP_UPDATE})
     */
    public $macAddress;

    /**
     * @var ?string OS version
     *
     * @Groups({Device::GROUP_READ_ITEM, Device::GROUP_UPDATE})
     */
    public $osVersion;

    /**
     * @var ?string OS version wanted (the device will self-update to this version)
     *
     * @Groups({Device::GROUP_READ_ITEM, Device::GROUP_UPDATE})
     */
    public $wantedOsVersion;

    /**
     * @var ?string Software version
     *
     * @Groups({Device::GROUP_READ_ITEM, Device::GROUP_UPDATE})
     *
     */
    public $softwareVersion;

    /**
     * @var ?string Software version wanted (the device will self-update to this version)
     *
     * @Groups({Device::GROUP_READ_ITEM, Device::GROUP_UPDATE})
     *
     */
    public $wantedSoftwareVersion;

    /**
     * @var bool SSH access enabled
     *
     * @Groups({Device::GROUP_READ_COLLECTION, Device::GROUP_UPDATE})
     */
    public $isSshEnabled;

    /**
     * @var bool VPN access enabled
     *
     * @Groups({Device::GROUP_READ_COLLECTION, Device::GROUP_UPDATE})
     */
    public $isVpnEnabled;

    /**
     * @var ?DeviceDiagnostic Contains frequently updated data (ping, last access...)
     *
     * @Groups({Device::GROUP_READ_ITEM})
     */
    public $diagnostic;

    /**
     * @var DeviceStatus Status of the device
     *
     * @Groups({Device::GROUP_READ_COLLECTION, Device::GROUP_UPDATE, Device::GROUP_UPDATE_STATUS})
     */
    public $status;

    /**
     * @var DeviceOutput[] Device video outputs
     * @Groups({Device::GROUP_READ_ITEM, Device::GROUP_CREATE})
     */
    public $outputs = [];

}

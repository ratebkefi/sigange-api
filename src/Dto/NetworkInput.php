<?php

namespace App\Dto;

use App\Entity\Network;
use App\Entity\Site;
use App\Entity\Tag;
use App\Entity\UserGroup;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Uid\Uuid;

final class NetworkInput
{

    /**
     * @var Uuid External identifier used by API
     *
     *
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_CREATE})
     */
    public $code;

    /**
     * An array of arrays where each first dimension index is the group tag name and the second dimensions arrays are
     * the corresponding tags.
     * tags
     * @var array<object, array<Tag>>
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_UPDATE})
     * @SerializedName("tags")
     */
    public array $tagsData = [];

    /**
     *
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_UPDATE, Network::GROUP_UPDATE_NAME})
     *
     */
    public $name;

    /**
     *
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_UPDATE, Network::GROUP_UPDATE_NAME})
     */
    public $description;

    /**
     *
     *
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_UPDATE})
     * @var UserGroup
     */
    public $userGroup;

    /**
     *
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_UPDATE})
     */
    public $publicIpV4;

    /**
     *
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_UPDATE})
     */
    public $publicIpV6;

    /**
     *
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_UPDATE})
     */
    public $gatewayIpV4;

    /**
     *
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_UPDATE})
     */
    public $gatewayIpV6;

    /**
     *
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_UPDATE})
     */
    public $ssh;

    /**
     *
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_UPDATE})
     */
    public $comment;


    /**
     *
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_UPDATE})
     * @var Site[]
     */
    public $sites = [];


    /**
     * @var bool Enable the network
     *
     * @Groups({Network::GROUP_READ_DEFAULT, Network::GROUP_UPDATE, Network::GROUP_UPDATE_ENABLED, Network::GROUP_UPDATE_DISABLED})
     */
    public $enabled = true;

}

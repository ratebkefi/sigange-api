<?php

namespace App\Dto;

use App\Entity\DeviceOutput;
use App\Entity\Screen;
use App\Entity\Site;
use App\Entity\Tag;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Uid\Uuid;

final class ScreenInput
{

    /**
     * @var Uuid External identifier used by API
     *
     *
     * @Groups({Screen::GROUP_READ_DEFAULT, Screen::GROUP_CREATE})
     */
    public $code;

    /**
     * An array of arrays where each first dimension index is the group tag name and the second dimensions arrays are
     * the corresponding tags.
     * tags
     * @var array<object, array<Tag>>
     * @Groups({Screen::GROUP_READ_DEFAULT, Screen::GROUP_UPDATE})
     * @SerializedName("tags")
     */
    public array $tagsData = [];

    /**
     *
     * @Groups({Screen::GROUP_READ_DEFAULT, Screen::GROUP_UPDATE, Screen::GROUP_UPDATE_NAME})
     */
    public $name;

    /**
     *
     * @Groups({Screen::GROUP_READ_DEFAULT, Screen::GROUP_UPDATE, Screen::GROUP_UPDATE_NAME})
     */
    public $description;

    /**
     *
     * @Groups({Screen::GROUP_READ_DEFAULT, Screen::GROUP_CREATE})
     * @var ?DeviceOutput
     */
    public $deviceOutput;


    /**
     *
     *
     * @Groups({Screen::GROUP_READ_DEFAULT, Screen::GROUP_CREATE})
     * @var ?Site
     */
    public $site;

    /**
     * @var bool Enable the screen
     *
     *
     *
     * @Groups({Screen::GROUP_READ_DEFAULT, Screen::GROUP_UPDATE, Screen::GROUP_UPDATE_ENABLED, Screen::GROUP_UPDATE_DISABLED})
     */
    public $enabled = true;

}

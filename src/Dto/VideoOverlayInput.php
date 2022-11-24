<?php

namespace App\Dto;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use App\Entity\Tag;
use App\Entity\UserGroup;
use App\Entity\VideoOverlay;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Uid\Uuid;

final class VideoOverlayInput
{


    /**
     * @var Uuid External identifier used by API
     *
     * @Groups({VideoOverlay::GROUP_READ_DEFAULT, VideoOverlay::GROUP_CREATE})
     */
    public $code;


    /**
     * An array of arrays where each first dimension index is the group tag name and the second dimensions arrays are
     * the corresponding tags.
     * tags
     * @var array<object, array<Tag>>
     * @Groups({VideoOverlay::GROUP_READ_DEFAULT, VideoOverlay::GROUP_UPDATE})
     * @SerializedName("tags")
     */
    public array $tagsData = [];

    /**
     * @Groups({VideoOverlay::GROUP_READ_DEFAULT, VideoOverlay::GROUP_UPDATE, VideoOverlay::GROUP_UPDATE_NAME})
     */
    public $name;

    /**
     * @Groups({VideoOverlay::GROUP_READ_DEFAULT, VideoOverlay::GROUP_UPDATE})
     */
    public $url;

    /**
     * @Groups({VideoOverlay::GROUP_READ_COLLECTION, VideoOverlay::GROUP_UPDATE})
     * @var UserGroup
     */
    public $userGroup;

    /**
     * @var bool Enable the video stream
     *
     * @ORM\Column(type="boolean")
     * @ApiFilter(BooleanFilter::class)
     * @Groups({VideoOverlay::GROUP_READ_DEFAULT, VideoOverlay::GROUP_UPDATE, VideoOverlay::GROUP_UPDATE_ENABLED, VideoOverlay::GROUP_UPDATE_DISABLED})
     */
    public $enabled = true;


}

<?php

namespace App\Dto;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use App\Entity\Tag;
use App\Entity\UserGroup;
use App\Entity\VideoStream;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Uid\Uuid;

final class VideoStreamInput
{


    /**
     * @var Uuid External identifier used by API
     *
     * @Groups({VideoStream::GROUP_READ_DEFAULT, VideoStream::GROUP_CREATE})
     */
    public $code;


    /**
     * An array of arrays where each first dimension index is the group tag name and the second dimensions arrays are
     * the corresponding tags.
     * tags
     * @var array<object, array<Tag>>
     * @Groups({VideoStream::GROUP_READ_DEFAULT, VideoStream::GROUP_UPDATE})
     * @SerializedName("tags")
     */
    public array $tagsData = [];

    /**
     * @Groups({VideoStream::GROUP_READ_DEFAULT, VideoStream::GROUP_UPDATE, VideoStream::GROUP_UPDATE_NAME})
     */
    public $name;

    /**
     * @Groups({VideoStream::GROUP_READ_DEFAULT, VideoStream::GROUP_UPDATE})
     */
    public $url;

    /**
     * @Groups({VideoStream::GROUP_READ_COLLECTION, VideoStream::GROUP_UPDATE})
     * @var UserGroup
     */
    public $userGroup;

    /**
     * @var bool Enable the video stream
     *
     * @ORM\Column(type="boolean")
     * @ApiFilter(BooleanFilter::class)
     * @Groups({VideoStream::GROUP_READ_DEFAULT, VideoStream::GROUP_UPDATE, VideoStream::GROUP_UPDATE_ENABLED, VideoStream::GROUP_UPDATE_DISABLED})
     */
    public $enabled = true;


}

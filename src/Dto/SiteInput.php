<?php

namespace App\Dto;

use App\Entity\Embeddable\Address;
use App\Entity\Embeddable\Contact;
use App\Entity\Network;
use App\Entity\Site;
use App\Entity\Tag;
use App\Entity\TemplateModel;
use App\Entity\UserGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Uid\Uuid;

final class SiteInput
{

    /**
     * @var Uuid External identifier used by API
     *
     * @Groups({Site::GROUP_READ_DEFAULT, Site::GROUP_CREATE})
     */
    public $code;

    /**
     * An array of arrays where each first dimension index is the group tag name and the second dimensions arrays are
     * the corresponding tags.
     * tags
     * @var array<object, array<Tag>>
     * @Groups({Site::GROUP_READ_DEFAULT, Site::GROUP_UPDATE})
     * @SerializedName("tags")
     */
    public array $tagsData = [];

    /**
     *
     * @Groups({Site::GROUP_READ_DEFAULT, Site::GROUP_UPDATE, Site::GROUP_UPDATE_NAME})
     */
    public $name;

    /**
     *
     * @Groups({Site::GROUP_READ_DEFAULT, Site::GROUP_UPDATE})
     */
    public $externalId;

    /**
     *
     * @Groups({Site::GROUP_READ_DEFAULT, Site::GROUP_UPDATE, Site::GROUP_UPDATE_NAME})
     */
    public $description;

    /**
     *
     *
     * @Groups({Site::GROUP_READ_DEFAULT, Site::GROUP_UPDATE})
     * @var UserGroup
     */
    public $userGroup;

    /**
     *
     * @Groups({Site::GROUP_READ_DEFAULT, Site::GROUP_UPDATE, Site::GROUP_CREATE})
     */
    public Address $address;

    /**
     *
     * @Groups({Site::GROUP_READ_DEFAULT, Site::GROUP_UPDATE, Site::GROUP_CREATE})
     */
    public Contact $contact;


    /**
     *
     * @Groups({Site::GROUP_UPDATE, Site::GROUP_READ_DEFAULT})
     * @var Network[]
     */
    public $networks;


    /**
     * @var bool Enable the site
     *
     * @Groups({Site::GROUP_READ_DEFAULT, Site::GROUP_UPDATE, Site::GROUP_UPDATE_ENABLED, Site::GROUP_UPDATE_DISABLED, Site::GROUP_READ_ITEM})
     */
    public bool $enabled = true;


    /**
     * @var ?TemplateModel
     * @Groups({Site::GROUP_READ_DEFAULT, Site::GROUP_UPDATE})
     */
    public $templateModel;

    public function __construct()
    {
        $this->contact = new Contact();
        $this->address = new Address();
        $this->networks = new ArrayCollection();
    }

}

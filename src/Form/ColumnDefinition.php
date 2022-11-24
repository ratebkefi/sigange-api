<?php

namespace App\Form;

use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\EntityDisplayCustomization;


class ColumnDefinition
{
    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @param string $propertyName
     */
    public function setPropertyName(string $propertyName): void
    {
        $this->propertyName = $propertyName;
    }

    /**
     * @param string $propertyType
     */
    public function setPropertyType(string $propertyType): void
    {
        $this->propertyType = $propertyType;
    }


    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    /**
     * @return string
     */
    public function getPropertyType(): string
    {
        return $this->propertyType;
    }

    /**
     * @return Boolean
     */
    public function getSortable()
    {
        return $this->sortable;
    }

    /**
     * @param Boolean $sortable
     * @return ColumnDefinition
     */
    public function setSortable($sortable)
    {
        $this->sortable = $sortable;
        return $this;
    }


    public function __toString()
    {
        return "$this->label . $this->propertyName . $this->propertyType";
    }

    public function __construct(string $label, string $propertyName, string $propertyType)
    {
        $this->label = $label;
        $this->propertyName = $propertyName;
        $this->propertyType = $propertyType;

    }

    /**
     * @Groups({EntityDisplayCustomization::GROUP_READ_DEFAULT, EntityDisplayCustomization::GROUP_UPDATE})
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="100")
     * @var string
     */
    public string $label;

    /**
     * @Groups({EntityDisplayCustomization::GROUP_READ_DEFAULT, EntityDisplayCustomization::GROUP_UPDATE})
     * @Assert\NotBlank()
     * @var string
     */
    public string $propertyType;

    /**
     * @Groups({EntityDisplayCustomization::GROUP_READ_DEFAULT, EntityDisplayCustomization::GROUP_UPDATE})
     * @Assert\NotBlank()
     * @var string
     */
    public string $propertyName;

    /**
     * @Groups({EntityDisplayCustomization::GROUP_READ_DEFAULT, EntityDisplayCustomization::GROUP_UPDATE})
     * @Assert\NotBlank()
     * @var boolean
     */
    public bool $sortable;


}

<?php


namespace App\Entity;


use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Validator\TagConstraint;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait TaggableTrait
{

    /**
     * @TagConstraint()
     * @ORM\ManyToMany(targetEntity=Tag::class)
     * @Groups({Tag::GROUP_UPDATE_RELATION, Tag::GROUP_READ_DEFAULT})
     * @ApiFilter(SearchFilter::class,
     *     properties={
     *     "tags.name": SearchFilter::STRATEGY_PARTIAL
     * })
     */
    private $tags;


    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function removeAllTags(): self
    {
        $this->tags = new ArrayCollection() ;

        return $this;
    }

    /**
     * Return an array of Tag Groups with their associated tags
     * @Groups({Tag::GROUP_READ_DEFAULT})
     * @return <string, Tag[]>[]
     */
    public function getTagGroups(): array
    {
        $result = [];
        $tagGroups = [];
        foreach ($this->tags as $tag) {
            $tagGroups[] = $tag->getTagGroup();
        }
        foreach ($tagGroups as $key => $group) {

            $code = $group->getCode()->toRfc4122();

            $result[$code]["code"] = $group->getCode();
            $result[$code]["name"] = $group->getName();
            $result[$code]["description"] = $group->getDescription();
            $result[$code]["options"] = $group->getOptions();

            foreach ($this->tags as $tag) {
                if ($tag->getTagGroup()->getId() === $group->getId()) {
                    $result[$code]["values"][] = $tag;
                }
            }
        }
        ksort($result);
        return $result;
    }
}

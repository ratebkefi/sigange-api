<?php

namespace App\Event;

use App\Interfaces\WatchableInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Example of a custom event to dispatch when an entity implementing WatchableInterface is "changed".
 * The whole entity is included in the event for future treatment.
 */
class EntityChangedEvent extends Event
{
    public const NAME = 'entity.changed';

    protected WatchableInterface $entity;

    public function __construct(WatchableInterface $entity)
    {
        $this->entity = $entity;
    }

    public function getEntity(): WatchableInterface
    {
        return $this->entity;
    }

    public function setEntity(WatchableInterface $entity): Event
    {
        $this->entity = $entity;
        return $this;
    }
}

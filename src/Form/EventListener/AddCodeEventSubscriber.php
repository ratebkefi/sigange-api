<?php

namespace App\Form\EventListener;

use App\Form\Type\UuidType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AddCodeEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [FormEvents::PRE_SET_DATA => 'preSetData'];
    }

    public function preSetData(FormEvent $event): void
    {
        $entity = $event->getData();
        $form = $event->getForm();

        // Only add code for a creation
        if (!$entity || null === $entity->getId()) {
            // TODO use Symfony\Component\Form\Extension\Core\Type\UuidType when symfony version upgrade to 5.3
            $form->add('code', UuidType::class, ["required" => true]);
        }
    }
}

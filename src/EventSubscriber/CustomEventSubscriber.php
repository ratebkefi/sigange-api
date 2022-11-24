<?php


namespace App\EventSubscriber;

use App\Entity\Webhook;
use App\Event\EntityChangedEvent;
use App\Interfaces\WatchableInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Process\Process;

/**
 * Subscribe to the custom events 'onEntityChanged'. Only treat the entities that implement WatchableInterface.
 * Then get all WebHooks corresponding to the entity and the event type 'entityChanged'
 * and call the command PostEntityCommand
 *
 * TODO: add an event for each pertinent situation and a corresponding method to handle this event and modify the treatment
 * Class CustomEventSubscriber
 * @package App\EventSubscriber
 */
class CustomEventSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    private array $pendingNotifications;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->pendingNotifications = array();

    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntityChangedEvent::NAME => 'onEntityChanged',
        ];
    }

    public function onEntityChanged(EntityChangedEvent $event)
    {
        $data = $event->getEntity();
        $event->setEntity($data);


        if (!$data instanceof WatchableInterface) {
            return;
        }

        $entityManager = $this->entityManager;

        // TODO: the following lines of codes are just an example. We could do any treatment needed here.

        // Only check the WebHook withe the pertinent event type
        $webhooks = $entityManager->getRepository(Webhook::class)
            ->findBy([
                'resourceClass' => get_class($data),
                'eventType' => 'entityChanged'
            ]);

        // Prepare the notifications to be sent to the url corresponding to the WebHook
        foreach ($webhooks as $webhook) {
            $this->pendingNotifications[] = [
                'code' => $data->getCode()->jsonSerialize(),
                'class' => get_class($data),
                'url' => $webhook->getUrl()
            ];
        }

        // Execute PostEntityCommand with pertinent data
        foreach ($this->pendingNotifications as $code => $pendingNotification) {
            $process = new Process(
                [
                    "bin/console",
                    "post:entity",
                    $pendingNotification['code'],
                    $pendingNotification['class'],
                    $pendingNotification['url']
                ],
            );
            $process->setWorkingDirectory(getcwd() . "/../");

            $process->start();
        }
        $this->pendingNotifications = [];
    }
}

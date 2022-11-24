<?php

namespace App\EventListener;

use App\Entity\Webhook;
use App\Interfaces\WebhookTriggeringInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Look through Webhook(App\Entity\Webhook) to find endpoint to notify some Entity has been updated
 * Look for webhook in postUpdate to know updated Entity class, store eligible webhook in a class property
 * On postFlush execute commands for each webhook. Need to wait post flush so when command is executed, requested data from database are up to date.
 * Class EntityUpdateNotifier
 * @package App\EventListener
 */
class EntityUpdateNotifier
{

    private array $pendingNotifications;
    private KernelInterface $kernel;
    private LoggerInterface $logger;

    public function __construct(KernelInterface $kernel, LoggerInterface $logger)
    {
        $this->pendingNotifications = array();
        $this->kernel = $kernel;
        $this->logger = $logger;
    }

    /**
     * Find webhooks targeting updated entity class
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        // TODO custom code according to class and fields changed
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();
        $uow->computeChangeSets();
        $changeset = $uow->getEntityChangeSet($entity);

        // if this listener only applies to certain entity types,
        // add some code to check the entity type as early as possible
        if (!$entity instanceof WebhookTriggeringInterface) {
            return;
        }

        $entityManager = $args->getObjectManager();

        $this->logger->info("EntityUpdateNotifier : " . get_class($entity) . " " . $entity->getCode()->toRfc4122());
        $this->logger->debug("EntityUpdateNotifier : " . get_class($entity) . " " . $entity->getName());
        $this->logger->debug("EntityUpdateNotifier : " . get_class($entity) . ". Updated fields: " . json_encode($changeset));

        $webhooks = $entityManager->getRepository(Webhook::class)
            ->findBy([
                'resourceClass' => get_class($entity),
                'eventType' => 'postUpdate'
            ]);

        foreach ($webhooks as $webhook) {
            $this->logger->info("EntityUpdateNotifier for webhook : " . $webhook->getCode()->toRfc4122());
            $this->logger->debug("EntityUpdateNotifier for webhook : " . $webhook->getName());

            $this->pendingNotifications[] = [
                'code' => $entity->getCode()->jsonSerialize(),
                'class' => get_class($entity),
                'url' => $webhook->getUrl()
            ];
        }

    }

    /**
     * Wait post flush to execute commands to have updated entities
     * @param PostFlushEventArgs $args
     * @throws \Exception
     */
    public function postFlush(PostFlushEventArgs $args): void
    {
        $kernel = $this->kernel;

        foreach ($this->pendingNotifications as $code => $pendingNotification) {
            $application = new Application($kernel);
            $application->setAutoExit(false);
            $input = new ArrayInput([
                'command' => 'post:entity',
                'code' => $pendingNotification['code'],
                'class' => $pendingNotification['class'],
                'url' => $pendingNotification['url'],
            ]);
            // You can use NullOutput() if you don't need the output
            $output = new BufferedOutput();
            $application->run($input, $output);
        }
        $this->pendingNotifications = [];
    }

}

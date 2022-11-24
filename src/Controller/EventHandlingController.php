<?php


namespace App\Controller;

use App\Event\EntityChangedEvent;
use App\Interfaces\WatchableInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class EventHandlingController extends BaseController
{

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($em, $serializer);
        $this->eventDispatcher = $eventDispatcher;
    }


    /**
     * Create a custom event
     *
     * TODO: allow to create several types of events (stored in a constant or in a new entity) by name or code in the
     * request body.
     *
     * @Route(
     *     name="post_entity_event",
     *     path="/api/entity_events",
     *     methods={"POST"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="post_entity_event",
     *      "_controller": "App\Controller\UserCustomController:patchSetIsSuperAdminAction",
     *     }
     * )
     * @Security("is_granted('ROLE_ENTITY_EVENT_POST_COLLECTION', object)")
     * @param Request $request
     * @return JsonResponse
     */
    public function postEventAction(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $em = $this->em;
        $entityClassName = 'App:' . ucfirst($data['entityClassName']);
        // TODO return 404 or 400 if no entity class name (or if not found in an enum of all class names?)
        $code = $data['code'] ?? null;
        if (!$code) {
            return $this->createJsonErrorResponse("Bad request", 'Missing code', Response::HTTP_BAD_REQUEST);
        }
        $entity = $em->getRepository($entityClassName)->findOneBy(["code" => $code]);
        if (!$entity) {
            return $this->createJsonErrorResponse("Bad request", 'Entity not found', Response::HTTP_NOT_FOUND);
        }

        if (!$entity instanceof WatchableInterface) {
            return $this->createJsonErrorResponse("Bad request", 'Entity not supported', Response::HTTP_BAD_REQUEST);
        }
        try {

            // Creates the Event and dispatches it
            $event = new EntityChangedEvent($entity);
            // For now only dispatch one type of event
            // TODO: creation of specific types of events from a determined list
            $this->eventDispatcher->dispatch($event, EntityChangedEvent::NAME);
            // TODO, what to return? maybe no need to return anything
            return $this->createSerializedResponse($event->getEntity());
        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

}

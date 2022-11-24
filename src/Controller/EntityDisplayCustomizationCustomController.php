<?php

namespace App\Controller;

use App\Entity\EntityDisplayCustomization;
use App\Form\Type\EntityDisplayCustomizationType;
use App\Repository\EntityDisplayCustomizationRepository;
use App\Service\EntityDisplayCustomizationHandler;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class EntityDisplayCustomizationCustomController extends BaseController
{
    protected EntityManagerInterface $em;
    protected SerializerInterface $serializer;

    private EntityDisplayCustomizationHandler $handler;
    private EntityDisplayCustomizationRepository $entityRepository;

    public function __construct(
        EntityDisplayCustomizationHandler $handler,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        EntityDisplayCustomizationRepository $entityRepository
    ) {
        parent::__construct($em, $serializer);
        $this->handler = $handler;
        $this->entityRepository = $entityRepository;

    }

    /**
     * @Route(
     *     name="api_entity_display_customizations_patch_custom",
     *     path="/api/entity_display_customizations/{code}",
     *     methods={"PATCH"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="api_entity_display_customization patch_custom",
     *      "_controller": "App\Controller\EntityDisplayCustomizationCustomController:patchEntityDisplayCustomizationCustomAction",
     *     }
     * )
     * @Security("is_granted('ROLE_ENTITY_DISPLAY_CUSTOMIZATION_PATCH_CUSTOM', object)")
     * @param Request $request
     * @param EntityDisplayCustomization $entityDisplayCustomization
     * @return JsonResponse
     */
    public function patchEntityDisplayCustomizationCustomAction(
        Request $request,
        EntityDisplayCustomization $entityDisplayCustomization
    ): JsonResponse {
        $this->handler->restrictAccessToOwner($this->getUser(), $entityDisplayCustomization);
        return $this->handleEntityDisplayCustomization($entityDisplayCustomization, $request);
    }

    /**
     * @Route(
     *     name="api_entity_display_customizations_post_collection_custom",
     *     path="/api/entity_display_customizations",
     *     methods={"POST"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="api_entity_display_customization_post_collection_custom",
     *      "_controller": "App\Controller\EntityDisplayCustomizationCustomController:createEntityDisplayCustomizationCustomAction",
     *     }
     * )
     * @Security("is_granted('ROLE_ENTITY_DISPLAY_CUSTOMIZATION_POST_CUSTOM', object)")
     * @param Request $request
     * @return JsonResponse
     */
    public function createEntityDisplayCustomizationCustomAction(Request $request): JsonResponse
    {
        return $this->handleEntityDisplayCustomization(new EntityDisplayCustomization(), $request);
    }

    /**
     * @Route(
     *     name="api_entity_display_customizations_put_collection_custom",
     *     path="/api/entity_display_customizations/{code}",
     *     methods={"PUT"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="api_entity_display_customization_put_collection_custom",
     *      "_controller": "App\Controller\EntityDisplayCustomizationCustomController:createEntityDisplayCustomizationCustomAction",
     *     }
     * )
     * @Security("is_granted('ROLE_ENTITY_DISPLAY_CUSTOMIZATION_PUT_CUSTOM', object)")
     * @param Request $request
     * @param EntityDisplayCustomization $entityDisplayCustomization
     * @return JsonResponse
     */
    public function updateEntityDisplayCustomizationCustomAction(
        Request $request,
        EntityDisplayCustomization $entityDisplayCustomization
    ): JsonResponse {
        $this->handler->restrictAccessToOwner($this->getUser(), $entityDisplayCustomization);

        return $this->handleEntityDisplayCustomization($entityDisplayCustomization, $request);

    }

    /**
     * @param EntityDisplayCustomization $entityDisplayCustomization
     * @return JsonResponse
     */
    public function handleDefault(
        EntityDisplayCustomization $entityDisplayCustomization
    ): JsonResponse {
        $em = $this->em;
        try {

            $this->entityRepository->handleDefaultStatus($entityDisplayCustomization);
            $em->flush();

            return $this->createSerializedResponse($entityDisplayCustomization);


        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @param EntityDisplayCustomization $entityDisplayCustomization
     * @param Request $request
     * @return JsonResponse
     */
    protected function handleEntityDisplayCustomization(
        EntityDisplayCustomization $entityDisplayCustomization,
        Request $request
    ): JsonResponse {
        $em = $this->em;
        try {
            $data = json_decode($request->getContent(), true);

            $method = $request->getMethod();
            $requestHasOwner = array_key_exists('owner', $data);
            $requestHasColumns = array_key_exists('columns', $data);
            if (($method === 'POST' || $method === 'PUT' || $method === 'PATCH') && $requestHasOwner) {
                // Gets the code directly or in an IRI
                $found = preg_match('/(?:(api\/users\/)?)([0-9a-f-]+)/', $data["owner"], $matches);
                try {
                    $owner = $found ? $em->getRepository('App:User')->findOneBy(["code" => $matches[2]]) : null;
                    if (!$owner) {
                        throw new NotFoundHttpException('Owner of the entity not found');
                    }
                    $entityDisplayCustomization->setOwner($owner);
                } catch (Exception $exception) {
                    throw new NotFoundHttpException('Owner of the entity not found');
                }
            }
            if ($requestHasColumns) {
                $entityDisplayCustomization->setColumns(!empty($data["columns"]) ? $data["columns"] : []);
            }

            $form = $this->createForm(EntityDisplayCustomizationType::class, $entityDisplayCustomization);
            $errors = $this->handler->handleForm($request, $form, $entityDisplayCustomization);

            if ($errors === 0) {
                $isDefault = array_key_exists('isDefault', $data) ? (bool)$data['isDefault'] : false;
                if ($isDefault === true) {
                    return $this->handleDefault($entityDisplayCustomization);
                }
                return $this->createSerializedResponse($entityDisplayCustomization);
            }

            return $this->createJsonErrorResponse("Bad request", $errors, Response::HTTP_BAD_REQUEST);

        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

}

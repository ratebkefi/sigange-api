<?php


namespace App\Controller;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class BaseController extends AbstractController
{

    protected EntityManagerInterface $em;
    protected SerializerInterface $serializer;

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $this->em = $em;
        $this->serializer = $serializer;
    }

    /**
     * Send ad JSON error response with a message and a string of errors with a specific HTTP Response code
     * @param string $message The error message
     * @param string $errors An error or a list of errors
     * @param int $code The HTTP status code
     * @return JsonResponse
     */
    protected function createJsonErrorResponse(
        string $message,
        string $errors,
        int $code = Response::HTTP_BAD_REQUEST
    ): JsonResponse {
        if ($code < 100 || $code > 511) {
            $code = Response::HTTP_BAD_REQUEST;
        }
        return new JsonResponse(['message' => $message, 'errors' => $errors], $code);
    }

    /**
     * Use a serialization group of the entity before creating a JSON Response with the serialized data
     * @param  $entity
     * @param string|null $serializationGroup
     * @return JsonResponse
     */
    protected function createSerializedResponse($entity, ?string $serializationGroup = "API_READ_DETAIL"): JsonResponse
    {
        try {
            $json = $this->serializer->serialize(
                $entity,
                'jsonld',
                // TODO add a specific Group for this return which list all useful fields but not the children
                constant(get_class($entity) . '::' . $serializationGroup)
            );
            return new JsonResponse((json_decode($json, true)));
        } catch (\Exception $exception) {
            throw new BadRequestException($exception->getMessage());
        }
    }

    /**
     * Process the form and update the resource, serialize the data and return it or send the appropriate error
     * @param Request $request
     * @param FormInterface $form
     * @param $entity
     * @return JsonResponse
     */
    protected function processForm(Request $request, FormInterface $form, $entity): JsonResponse
    {
        $method = $request->getMethod();

        $data = json_decode($request->getContent(), true);
        $clearMissing = $method !== 'PATCH';
        $em = $this->em;
        $form->submit($data, $clearMissing);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($entity);
            $em->flush();
        } else {
            $errors = (string)$form->getErrors(true, false);
            return $this->createJsonErrorResponse("Bad request", $errors, Response::HTTP_BAD_REQUEST);
        }

        return $this->createSerializedResponse($entity);
    }
}

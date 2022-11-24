<?php


namespace App\Controller;

use App\Entity\Screen;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ScreenCustomController extends BaseController
{


    /**
     * @Route(
     *     name="api_screens_patch_enable",
     *     path="/api/screens/{code}/enable",
     *     methods={"PATCH"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="api_screens_patch_enable",
     *      "_controller": "App\Controller\ScreenCustomController:patchScreenEnableAction",
     *     }
     * )
     * @Security("is_granted('ROLE_SCREEN_PATCH_ENABLE', object)")
     * @param Screen $screen
     * @return JsonResponse
     */
    public function patchScreenEnableAction(Screen $screen): JsonResponse
    {
        $em = $this->em;

        try {

            $screen->setEnabled(true);

            $em->persist($screen);

            $em->flush();

            return $this->createSerializedResponse($screen, 'API_PATCH_ENABLED');
        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }


    /**
     * @Route(
     *     name="api_screens_patch_disable",
     *     path="/api/screens/{code}/disable",
     *     methods={"PATCH"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="api_screens_patch_disable",
     *      "_controller": "App\Controller\ScreenCustomController:patchDeviceDisableAction",
     *     }
     * )
     * @Security("is_granted('ROLE_SCREEN_PATCH_DISABLE', object)")
     * @param Screen $screen
     * @return JsonResponse
     */
    public function patchScreenDisableAction(Screen $screen): JsonResponse
    {

        try {

            $screen->setEnabled(false);

            $em = $this->em;
            $em->persist($screen);

            $em->flush();

            return $this->createSerializedResponse($screen, 'API_PATCH_DISABLED');
        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), 400);
        }
    }


}

<?php


namespace App\Controller;

use App\Entity\VideoOverlay;
use App\Security\Voter\UserGroupVisibilityVoter;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VideoOverlayCustomController extends BaseController
{


    /**
     * @Route(
     *     name="api_video_overlays_patch_enable",
     *     path="/api/video_overlays/{code}/enable",
     *     methods={"PATCH"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="api_video_overlays_patch_enable",
     *      "_controller": "App\Controller\VideoOverlayCustomController:patchVideoOverlayEnableAction",
     *     }
     * )
     * @Security("is_granted('ROLE_VIDEO_OVERLAY_PATCH_ENABLE', object)")
     * @param VideoOverlay $videoOverlay
     * @return JsonResponse
     */
    public function patchVideoOverlayEnableAction(VideoOverlay $videoOverlay): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserGroupVisibilityVoter::class, $videoOverlay);
        $em = $this->em;

        try {

            $videoOverlay->setEnabled(true);

            $em->persist($videoOverlay);

            $em->flush();

            return $this->createSerializedResponse($videoOverlay, 'API_PATCH_ENABLED');
        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }


    /**
     * @Route(
     *     name="api_video_overlays_patch_disable",
     *     path="/api/video_overlays/{code}/disable",
     *     methods={"PATCH"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="api_video_overlays_patch_disable",
     *      "_controller": "App\Controller\VideoOverlayCustomController:patchVideoOverlayDisableAction",
     *     }
     * )
     * @Security("is_granted('ROLE_VIDEO_OVERLAY_PATCH_DISABLE', object)")
     * @param VideoOverlay $videoOverlay
     * @return JsonResponse
     */
    public function patchVideoOverlayDisableAction(VideoOverlay $videoOverlay): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserGroupVisibilityVoter::class, $videoOverlay);

        try {

            $videoOverlay->setEnabled(false);

            $em = $this->em;
            $em->persist($videoOverlay);

            $em->flush();

            return $this->createSerializedResponse($videoOverlay, 'API_PATCH_DISABLED');
        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), 400);
        }
    }

}

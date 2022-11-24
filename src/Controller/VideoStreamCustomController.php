<?php


namespace App\Controller;

use App\Entity\VideoStream;
use App\Security\Voter\UserGroupVisibilityVoter;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VideoStreamCustomController extends BaseController
{


    /**
     * @Route(
     *     name="api_video_streams_patch_enable",
     *     path="/api/video_streams/{code}/enable",
     *     methods={"PATCH"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="api_video_streams_patch_enable",
     *      "_controller": "App\Controller\VideoStreamCustomController:patchVideoStreamEnableAction",
     *     }
     * )
     * @Security("is_granted('ROLE_VIDEO_STREAM_PATCH_ENABLE', object)")
     * @param VideoStream $videoStream
     * @return JsonResponse
     */
    public function patchVideoStreamEnableAction(VideoStream $videoStream): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserGroupVisibilityVoter::class, $videoStream);

        $em = $this->em;

        try {

            $videoStream->setEnabled(true);

            $em->persist($videoStream);

            $em->flush();

            return $this->createSerializedResponse($videoStream, 'API_PATCH_ENABLED');
        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }


    /**
     * @Route(
     *     name="api_video_streams_patch_disable",
     *     path="/api/video_streams/{code}/disable",
     *     methods={"PATCH"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="api_video_streams_patch_disable",
     *      "_controller": "App\Controller\VideoStreamCustomController:patchVideoStreamDisableAction",
     *     }
     * )
     * @Security("is_granted('ROLE_VIDEO_STREAM_PATCH_DISABLE', object)")
     * @param VideoStream $videoStream
     * @return JsonResponse
     */
    public function patchVideoStreamDisableAction(VideoStream $videoStream): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserGroupVisibilityVoter::class, $videoStream);

        try {

            $videoStream->setEnabled(false);

            $em = $this->em;
            $em->persist($videoStream);

            $em->flush();

            return $this->createSerializedResponse($videoStream, 'API_PATCH_DISABLED');
        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), 400);
        }
    }

}

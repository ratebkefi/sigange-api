<?php


namespace App\Controller;

use App\Entity\Platform;
use App\Security\Voter\UserGroupVisibilityVoter;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlatformCustomController extends BaseController
{


    /**
     * @Route(
     *     name="api_platforms_patch_enable",
     *     path="/api/platforms/{code}/enable",
     *     methods={"PATCH"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="api_platforms_patch_enable",
     *      "_controller": "App\Controller\PlatformCustomController:patchPlatformEnableAction",
     *     }
     * )
     * @Security("is_granted('ROLE_PLATFORM_PATCH_ENABLE', object)")
     * @param Platform $platform
     * @return JsonResponse
     */
    public function patchPlatformEnableAction(Platform $platform): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserGroupVisibilityVoter::class, $platform);

        $em = $this->em;

        try {

            $platform->setEnabled(true);

            $em->persist($platform);

            $em->flush();

            return $this->createSerializedResponse($platform, 'API_PATCH_ENABLED');
        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }


    /**
     * @Route(
     *     name="api_platforms_patch_disable",
     *     path="/api/platforms/{code}/disable",
     *     methods={"PATCH"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="api_platforms_patch_disable",
     *      "_controller": "App\Controller\PlatformCustomController:patchPlatformDisableAction",
     *     }
     * )
     * @Security("is_granted('ROLE_PLATFORM_PATCH_DISABLE', object)")
     * @param Platform $platform
     * @return JsonResponse
     */
    public function patchPlatformDisableAction(Platform $platform): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserGroupVisibilityVoter::class, $platform);

        try {

            $platform->setEnabled(false);

            $em = $this->em;
            $em->persist($platform);

            $em->flush();

            return $this->createSerializedResponse($platform, 'API_PATCH_DISABLED');
        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), 400);
        }
    }

}

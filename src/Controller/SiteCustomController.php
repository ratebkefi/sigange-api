<?php


namespace App\Controller;

use App\Entity\Site;
use App\Security\Voter\UserGroupVisibilityVoter;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SiteCustomController extends BaseController
{


    /**
     * @Route(
     *     name="api_sites_patch_enable",
     *     path="/api/sites/{code}/enable",
     *     methods={"PATCH"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="api_sites_patch_enable",
     *      "_controller": "App\Controller\SiteCustomController:patchSiteEnableAction",
     *     }
     * )
     * @Security("is_granted('ROLE_SITE_PATCH_ENABLE', object)")
     * @param Site $site
     * @return JsonResponse
     */
    public function patchSiteEnableAction(Site $site): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserGroupVisibilityVoter::class, $site);

        $em = $this->em;

        try {

            $site->setEnabled(true);

            $em->persist($site);

            $em->flush();

            return $this->createSerializedResponse($site, 'API_PATCH_ENABLED');
        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }


    /**
     * @Route(
     *     name="api_sites_patch_disable",
     *     path="/api/sites/{code}/disable",
     *     methods={"PATCH"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="api_sites_patch_disable",
     *      "_controller": "App\Controller\SiteCustomController:patchDeviceDisableAction",
     *     }
     * )
     * @Security("is_granted('ROLE_SITE_PATCH_DISABLE', object)")
     * @param Site $site
     * @return JsonResponse
     */
    public function patchSiteDisableAction(Site $site): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserGroupVisibilityVoter::class, $site);

        try {

            $site->setEnabled(false);

            $em = $this->em;
            $em->persist($site);

            $em->flush();

            return $this->createSerializedResponse($site, 'API_PATCH_DISABLED');
        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), 400);
        }
    }


}

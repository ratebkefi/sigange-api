<?php


namespace App\Controller;

use App\Entity\Network;
use App\Security\Voter\UserGroupVisibilityVoter;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NetworkCustomController extends BaseController
{


    /**
     * @Route(
     *     name="api_networks_patch_enable",
     *     path="/api/networks/{code}/enable",
     *     methods={"PATCH"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="api_networks_patch_enable",
     *      "_controller": "App\Controller\NetworkCustomController:patchNetworkEnableAction",
     *     }
     * )
     * @Security("is_granted('ROLE_NETWORK_PATCH_ENABLE', object)")
     * @param Network $network
     * @return JsonResponse
     */
    public function patchNetworkEnableAction(Network $network): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserGroupVisibilityVoter::class, $network);

        $em = $this->em;

        try {

            $network->setEnabled(true);

            $em->persist($network);

            $em->flush();

            return $this->createSerializedResponse($network, 'API_PATCH_ENABLED');
        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }


    /**
     * @Route(
     *     name="api_networks_patch_disable",
     *     path="/api/networks/{code}/disable",
     *     methods={"PATCH"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="api_networks_patch_disable",
     *      "_controller": "App\Controller\NetworkCustomController:patchNetworkDisableAction",
     *     }
     * )
     * @Security("is_granted('ROLE_NETWORK_PATCH_DISABLE', object)")
     * @param Network $network
     * @return JsonResponse
     */
    public function patchNetworkDisableAction(Network $network): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserGroupVisibilityVoter::class, $network);

        try {

            $network->setEnabled(false);

            $em = $this->em;
            $em->persist($network);

            $em->flush();

            return $this->createSerializedResponse($network, 'API_PATCH_DISABLED');
        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), 400);
        }
    }

}

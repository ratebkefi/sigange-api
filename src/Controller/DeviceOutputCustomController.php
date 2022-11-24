<?php


namespace App\Controller;

use App\Entity\DeviceOutput;
use App\Security\Voter\UserGroupVisibilityVoter;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeviceOutputCustomController extends BaseController
{


    /**
     * @Route(
     *     name="api_device_outputs_patch_enable",
     *     path="/api/device_outputs/{code}/enable",
     *     methods={"PATCH"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="api_device_outputs_patch_enable",
     *      "_controller": "App\Controller\DeviceOutputCustomController:patchDeviceOutputEnableAction",
     *     }
     * )
     * @Security("is_granted('ROLE_DEVICE_OUTPUT_PATCH_ENABLE', object)")
     * @param DeviceOutput $deviceOutput
     * @return JsonResponse
     */
    public function patchDeviceOutputEnableAction(DeviceOutput $deviceOutput): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserGroupVisibilityVoter::class, $deviceOutput);

        $em = $this->em;

        try {

            $deviceOutput->setEnabled(true);

            $em->persist($deviceOutput);

            $em->flush();

            return $this->createSerializedResponse($deviceOutput, 'API_PATCH_ENABLED');
        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }


    /**
     * @Route(
     *     name="api_device_outputs_patch_disable",
     *     path="/api/device_outputs/{code}/disable",
     *     methods={"PATCH"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="api_device_outputs_patch_disable",
     *      "_controller": "App\Controller\DeviceOutputCustomController:patchDeviceOutputDisableAction",
     *     }
     * )
     * @Security("is_granted('ROLE_DEVICE_OUTPUT_PATCH_DISABLE', object)")
     * @param DeviceOutput $deviceOutput
     * @return JsonResponse
     */
    public function patchDeviceOutputDisableAction(DeviceOutput $deviceOutput): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserGroupVisibilityVoter::class, $deviceOutput);

        try {

            $deviceOutput->setEnabled(false);

            $em = $this->em;
            $em->persist($deviceOutput);

            $em->flush();

            return $this->createSerializedResponse($deviceOutput, 'API_PATCH_DISABLED');
        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), 400);
        }
    }

}

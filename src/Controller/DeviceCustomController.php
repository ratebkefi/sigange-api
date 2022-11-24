<?php


namespace App\Controller;

use App\Entity\Device;
use App\Entity\DeviceDiagnostic;
use App\Security\Voter\UserGroupVisibilityVoter;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class DeviceCustomController extends BaseController
{

    /**
     * @Route(
     *     name="api_devices_patch_enable",
     *     path="/api/devices/{code}/enable",
     *     methods={"PATCH"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="api_devices_patch_enable",
     *      "_controller": "App\Controller\DeviceCustomController:patchDeviceEnableAction",
     *     }
     * )
     * @Security("is_granted('ROLE_DEVICE_PATCH_ENABLE', object)")
     * @param Device $device
     * @return JsonResponse
     */
    public function patchDeviceEnableAction(Device $device): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserGroupVisibilityVoter::class, $device);
        $em = $this->em;

        try {

            $device->setEnabled(true);

            $em->persist($device);

            $em->flush();

            return $this->createSerializedResponse($device, 'API_PATCH_ENABLED');
        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }


    /**
     * @Route(
     *     name="api_devices_patch_disable",
     *     path="/api/devices/{code}/disable",
     *     methods={"PATCH"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="api_devices_patch_disable",
     *      "_controller": "App\Controller\DeviceCustomController:patchDeviceDisableAction",
     *     }
     * )
     * @Security("is_granted('ROLE_DEVICE_PATCH_DISABLE', object)")
     * @param Device $device
     * @return JsonResponse
     */
    public function patchDeviceDisableAction(Device $device): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserGroupVisibilityVoter::class, $device);

        try {

            $device->setEnabled(false);

            $em = $this->em;
            $em->persist($device);

            $em->flush();

            return $this->createSerializedResponse($device, 'API_PATCH_DISABLED');
        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), 400);
        }
    }


    /**
     * Set userGroup to null, set site to null, set status to Removed, set name to the macAddress value of the device
     * @Route(
     *     name="api_devices_patch_remove_group",
     *     path="/api/devices/{code}/remove_group",
     *     methods={"PATCH"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="api_devices_patch_remove_group",
     *      "_controller": "App\Controller\DeviceCustomController:patchDeviceRemoveGroupAction",
     *     }
     * )
     * @Security("is_granted('ROLE_DEVICE_PATCH_REMOVE_GROUP', object)")
     * @param Device $device
     * @return JsonResponse
     *
     */
    public function patchDeviceRemoveGroupAction(Device $device): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserGroupVisibilityVoter::class, $device);

        $em = $this->em;
        // Reset the status of the Device
        $device->setUserGroup(null);
        $device->setSite(null);
        $newStatus = $em->getRepository('App:DeviceStatus')->findOneBy(["name" => 'Removed']);
        $device->setStatus($newStatus);
        $device->setName($device->getMacAddress());

        $em->persist($device);

        $em->flush();


        return $this->createSerializedResponse($device, 'API_PATCH_REMOVE_GROUP');
    }

    /**
     * Get the device outputs of a device identified by its macAddress. The device outputs are only returned if they
     * are fully enabled.
     * Each time the endpoint is called, update the lastHttpConnection on the DeviceDiagnostic of the Device.
     * @Route(
     *     name="api_devices_get_enabled_outputs",
     *     path="/api/devices/{macAddress}/outputs",
     *     methods={"GET"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="api_devices_get_enabled_outputs",
     *      "_controller": "App\Controller\DeviceCustomController:getDeviceOutputsEnabledAction",
     *     }
     * )
     * @Security("is_granted('ROLE_DEVICE_GET_ENABLED_OUTPUTS', object)")
     * @param Device $device
     * @return JsonResponse
     */
    public function getDeviceOutputsEnabledAction(Device $device): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserGroupVisibilityVoter::class, $device);

        $deviceDiagnostic = $device->getDiagnostic();
        if ($deviceDiagnostic) {
            $deviceDiagnostic->setLastHttpConnectionAt(new \DateTime());
        } else {
            $deviceDiagnostic = (new DeviceDiagnostic())
                ->setCode(Uuid::v4())
                ->setLastHttpConnectionAt(new \DateTime())
                ->setDevice($device);

            $device->setDiagnostic($deviceDiagnostic);
        }
        $em = $this->em;
        $em->persist($deviceDiagnostic);
        $em->flush();
        return $this->createSerializedResponse($device, 'API_READ_OUTPUTS');
    }

}

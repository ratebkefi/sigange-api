<?php


namespace App\Controller;

use App\Entity\Customer;
use App\Security\Voter\UserGroupVisibilityVoter;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CustomerCustomController extends BaseController
{


    /**
     * @Route(
     *     name="api_customers_patch_enable",
     *     path="/api/customers/{code}/enable",
     *     methods={"PATCH"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="api_customers_patch_enable",
     *      "_controller": "App\Controller\CustomerCustomController:patchCustomerEnableAction",
     *     }
     * )
     * @Security("is_granted('ROLE_CUSTOMER_PATCH_ENABLE', object)")
     * @param Customer $customer
     * @return JsonResponse
     */
    public function patchCustomerEnableAction(Customer $customer): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserGroupVisibilityVoter::class, $customer);

        $em = $this->em;

        try {

            $customer->setEnabled(true);

            $em->persist($customer);

            $em->flush();

            return $this->createSerializedResponse($customer, 'API_PATCH_ENABLED');
        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }


    /**
     * @Route(
     *     name="api_customers_patch_disable",
     *     path="/api/customers/{code}/disable",
     *     methods={"PATCH"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="api_customers_patch_disable",
     *      "_controller": "App\Controller\CustomerCustomController:patchCustomerDisableAction",
     *     }
     * )
     * @Security("is_granted('ROLE_CUSTOMER_PATCH_DISABLE', object)")
     * @param Customer $customer
     * @return JsonResponse
     */
    public function patchCustomerDisableAction(Customer $customer): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserGroupVisibilityVoter::class, $customer);

        try {

            $customer->setEnabled(false);

            $em = $this->em;
            $em->persist($customer);

            $em->flush();

            return $this->createSerializedResponse($customer, 'API_PATCH_DISABLED');
        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), 400);
        }
    }

}

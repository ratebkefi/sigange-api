<?php


namespace App\Controller;

use App\Entity\User;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserCustomController extends BaseController
{

    /**
     * @Route(
     *     name="patch_set_is_super_admin",
     *     path="/api/users/{code}/set_is_super_admin",
     *     methods={"PATCH"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="patch_set_is_super_admin",
     *      "_controller": "App\Controller\UserCustomController:patchSetIsSuperAdminAction",
     *     }
     * )
     * @Security("is_granted('ROLE_SUPER_ADMIN', object)")
     * @param User $user
     * @return JsonResponse
     */
    public function patchSetIsSuperAdminAction(User $user): JsonResponse
    {
        $em = $this->em;

        try {

            $user->setIsSuperAdmin(true);

            $em->persist($user);

            $em->flush();

            return $this->createSerializedResponse($user, 'API_PATCH_SET_SUPER_ADMIN');
        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route(
     *     name="patch_unset_is_super_admin",
     *     path="/api/users/{code}/unset_is_super_admin",
     *     methods={"PATCH"},
     *     format= "jsonld",
     *     defaults={
     *      "_api_item_operation_name"="patch_unset_is_super_admin",
     *      "_controller": "App\Controller\UserCustomController:patchUnsetIsSuperAdminAction",
     *     }
     * )
     * @Security("is_granted('ROLE_SUPER_ADMIN', object)")
     * @param User $user
     * @return JsonResponse
     */
    public function patchUnsetIsSuperAdminAction(User $user): JsonResponse
    {
        $em = $this->em;

        try {

            $user->setIsSuperAdmin(false);

            $em->persist($user);

            $em->flush();

            return $this->createSerializedResponse($user, 'API_PATCH_UNSET_SUPER_ADMIN');
        } catch (Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }


}

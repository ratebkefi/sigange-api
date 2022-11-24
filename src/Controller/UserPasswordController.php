<?php


namespace App\Controller;


use App\Entity\User;
use App\Form\Type\UserPasswordType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserPasswordController extends BaseController
{
    /**
     * @Route(
     *     name="patch_password",
     *     path="/api/users/{code}/password",
     *     methods={"PATCH"},
     *     format= "json",
     *     defaults={
     *      "_api_item_operation_name"="patch_password",
     *      "_controller": "App\Controller\UserPasswordController:patchPasswordAction",
     *     }
     * )
     * Security("object == user")
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function patchPasswordAction(Request $request, User $user): JsonResponse
    {
        try {
            $form = $this->createForm(UserPasswordType::class, $user);
            return $this->processForm($request, $form, $user);
        } catch (\Exception $exception) {
            return $this->createJsonErrorResponse("Bad request", $exception->getMessage(), 400);
        }

    }
}

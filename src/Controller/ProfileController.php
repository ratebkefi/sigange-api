<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends BaseController
{


    /**
     * @Route(
     *     name="get_user_profile",
     *     path="/api/profile",
     *     methods={"GET"},
     *     format= "json",
     *     defaults={
     *      "_api_item_operation_name"="get_user_profile",
     *      "_controller": "App\Controller\ProfileController:getProfileAction",
     *     }
     * )
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @return JsonResponse
     */
    public function getProfileAction(): JsonResponse
    {

        return $this->createSerializedResponse($this->getUser());

    }


}

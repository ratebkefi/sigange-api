<?php

namespace App\Service;

use App\Entity\EntityDisplayCustomization;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class EntityDisplayCustomizationHandler
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * // TODO, replace by a Voter
     * @param UserInterface $user
     * @param EntityDisplayCustomization $entityDisplayCustomization
     */
    public function restrictAccessToOwner(
        UserInterface $user,
        EntityDisplayCustomization $entityDisplayCustomization
    ): void {
        if ($user !== $entityDisplayCustomization->getOwner()) {
            throw new AccessDeniedHttpException('Access denied');
        }
    }

    /**
     * Process the form and return 0 or a string with appropriate errors
     * @param Request $request
     * @param FormInterface $form
     * @param $entity
     * @return bool|int
     */
    public function handleForm(Request $request, FormInterface $form, $entity)
    {
        $data = json_decode($request->getContent(), true);
        $method = $request->getMethod();

        $clearMissing = $method !== 'PATCH';
        $em = $this->entityManager;
        $form->submit($data, $clearMissing);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($entity);
            $em->flush();
        } else {
            return (string)$form->getErrors(true, false);
        }
        return 0;
    }
}

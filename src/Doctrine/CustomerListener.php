<?php


namespace App\Doctrine;


use App\Entity\Customer;
use App\Entity\UserGroup;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Uid\Uuid;

class CustomerListener
{
    public function prePersist(Customer $customer, LifecycleEventArgs $args)
    {

        $entityManager = $args->getObjectManager();

        // Create a UserGroup on Customer creation
        if ($customer) {
            $userGroup = (new UserGroup())
                ->setCode(Uuid::v4())
                ->setName($customer->getName())
                ->setDescription($customer->getDescription());

            $entityManager->persist($userGroup);

            $customer->addUserGroup($userGroup);

        }
    }
}

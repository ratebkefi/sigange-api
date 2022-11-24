<?php

namespace App\Doctrine;


use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Customer;
use App\Entity\Device;
use App\Entity\DeviceOutput;
use App\Entity\Network;
use App\Entity\Platform;
use App\Entity\Site;
use App\Entity\Tag;
use App\Entity\TagGroup;
use App\Entity\TemplateModel;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Entity\VideoOverlay;
use App\Entity\VideoStream;
use App\Entity\Webhook;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

/**
 * Class UserGroupExtension
 *
 * Filter the queries according to the UserGroup of the connected User for the relevant entities.
 * Used for GET collection operations.
 * Don't filter the query if the user is an Admin or Super Admin
 *
 * @package App\Doctrine
 */
final class UserGroupExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ): void {
        $this->addWhereRestricted($queryBuilder, $resourceClass);
    }

    private function addWhereRestricted(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        /* @var User $user */
        $user = $this->security->getUser();
        if ($user === null) {
            return;
        }

        $userRoles = $user->getUserRoles();

        $isAdmin = $userRoles->exists(function ($key, $role) {
            return $role->getRoleName() === 'ROLE_ADMIN';
        });
        // Don't filter if the user is ADMIN or SUPER_ADMIN
        if ($isAdmin || $user->isSuperAdmin()) {
            return;
        }


        // Filter only entities that have a UserGroup relation
        // TODO use a global Doctrine ORM filter: https://api-platform.com/docs/core/filters/#using-doctrine-orm-filters
        if ($resourceClass !== Network::class &&
            $resourceClass !== Platform::class &&
            $resourceClass !== Site::class &&
            $resourceClass !== VideoOverlay::class &&
            $resourceClass !== VideoStream::class &&
            $resourceClass !== Customer::class &&
            $resourceClass !== UserGroup::class &&
            $resourceClass !== Device::class &&
            $resourceClass !== DeviceOutput::class &&
            $resourceClass !== Webhook::class &&
            $resourceClass !== TemplateModel::class &&
            $resourceClass !== TagGroup::class &&
            $resourceClass !== Tag::class) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $groupsOfTheUser = $this->getAllUserGroups($user);
        // Special case for Device (relation to UserGroup is via the Site)
        if ($resourceClass === Device::class) {
            // Get the UserGroups for the Site or from the Device (some have no Site)
            $queryBuilder
                ->leftJoin(sprintf('%s.site', $rootAlias), 'site')
                ->andWhere("site.userGroup IN (:groupsOfTheUser) OR " . sprintf('%s.userGroup', $rootAlias)
                    . " IN (:groupsOfTheUser)")
                ->setParameter("groupsOfTheUser", $groupsOfTheUser);

        } elseif ($resourceClass === Customer::class) {
            // Special case for Customer (field is a collection of UserGroups)
            $queryBuilder
                ->innerJoin(sprintf('%s.userGroups', $rootAlias), 'groups')
                ->andWhere("groups IN (:groupsOfTheUser)")
                ->setParameter("groupsOfTheUser", $groupsOfTheUser);
        } elseif ($resourceClass === UserGroup::class) {
            // Special case for UserGroup
            $queryBuilder
                ->leftJoin(sprintf('%s.customer', $rootAlias), 'customer')
                ->andWhere(sprintf("%s IN (:groupsOfTheUser)", $rootAlias) .
                    " OR customer IS NULL")
                ->setParameter("groupsOfTheUser", $groupsOfTheUser);
        } elseif ($resourceClass === DeviceOutput::class) {
            // Special case for DeviceOutput (relation to UserGroup is via the Device)
            $queryBuilder
                ->leftJoin(sprintf('%s.device', $rootAlias), 'device')
                ->andWhere("device.userGroup IN (:groupsOfTheUser)")
                ->setParameter("groupsOfTheUser", $groupsOfTheUser);
        } elseif ($resourceClass === Tag::class) {
            // Special case for Tag (relation to UserGroup is via the TagGroup)
            $queryBuilder
                ->leftJoin(sprintf('%s.tagGroup', $rootAlias), 'tagGroup')
                ->andWhere("tagGroup.userGroup IN (:groupsOfTheUser) OR tagGroup.userGroup IS NULL")
                ->setParameter("groupsOfTheUser", $groupsOfTheUser);
        } else {
            $queryBuilder
                ->andWhere(sprintf("%s.userGroup IN (:groupsOfTheUser)",
                        $rootAlias) . " OR " . sprintf("%s.userGroup IS NULL", $rootAlias))
                ->setParameter("groupsOfTheUser", $groupsOfTheUser);
        }

    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        string $operationName = null,
        array $context = []
    ): void {
        $this->addWhereRestricted($queryBuilder, $resourceClass);
    }

    /**
     * @return Collection|UserGroup[]
     */
    public function getAllUserGroups(User $user): Collection
    {
        $allUserGroups = new ArrayCollection();

        foreach ($user->getGroups() as $userGroup) {
            $allUserGroups[] = $userGroup;
            foreach ($this->getAllChildren($userGroup) as $childGroup) {
                $allUserGroups[] = $childGroup;
            }
        }
        return $allUserGroups;
    }

    /**
     * @return Collection|self[]
     */
    public function getAllChildren(UserGroup $userGroup): array
    {
        $allChildren = [];

        foreach ($userGroup->getChildren() as $child) {
            $allChildren[] = $child;
            $descendants = $this->getAllChildren($child);
            $allChildren = array_merge($allChildren, $descendants);
        }

        return $allChildren;
    }
}

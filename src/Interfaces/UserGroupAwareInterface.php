<?php
namespace App\Interfaces;

use Doctrine\Common\Collections\Collection;

interface UserGroupAwareInterface {
    public function getUserGroups() : Collection;
}

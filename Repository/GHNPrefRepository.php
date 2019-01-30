<?php

namespace Plugin\GHNDelivery\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\GHNDelivery\Entity\GHNPref;

/**
 * Class GHNPrefRepository
 * @package Plugin\GHNDelivery\Repository
 */
class GHNPrefRepository extends AbstractRepository
{
    public function __construct(\Doctrine\Common\Persistence\ManagerRegistry $registry, string $entityClass = GHNPref::class)
    {
        parent::__construct($registry, $entityClass);
    }
}

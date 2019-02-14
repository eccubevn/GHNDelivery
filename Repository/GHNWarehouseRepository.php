<?php
/**
 * Author: lqdung1992@gmail.com
 * Date: 2/14/2019
 * Time: 5:07 PM
 */

namespace Plugin\GHNDelivery\Repository;


use Doctrine\Common\Persistence\ManagerRegistry;
use Eccube\Entity\BaseInfo;
use Eccube\Repository\AbstractRepository;
use Eccube\Repository\BaseInfoRepository;
use Plugin\GHNDelivery\Entity\GHNWarehouse;

class GHNWarehouseRepository extends AbstractRepository
{
    /**
     * @var BaseInfo
     */
    private $baseInfo;
    
    public function __construct(ManagerRegistry $registry, string $entityClass = GHNWarehouse::class, BaseInfoRepository $baseInfoRepository)
    {
        $this->baseInfo = $baseInfoRepository->get();
        parent::__construct($registry, $entityClass);
    }

    /**
     * @return null|object
     */
    public function getByOne()
    {
        $Warehouse = $this->findOneBy([]);

        if (is_null($Warehouse)) {
            $Warehouse = new GHNWarehouse();
            $Warehouse->setEmail($this->baseInfo->getEmail02());
        }

        return $Warehouse;
    }
}
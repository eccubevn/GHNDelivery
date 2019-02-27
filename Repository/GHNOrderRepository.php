<?php
/**
 * Author: lqdung1992@gmail.com
 * Date: 2/15/2019
 * Time: 4:27 PM
 */

namespace Plugin\GHNDelivery\Repository;


use Doctrine\Common\Persistence\ManagerRegistry;
use Eccube\Repository\AbstractRepository;
use Plugin\GHNDelivery\Entity\GHNOrder;

class GHNOrderRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry, string $entityClass = GHNOrder::class)
    {
        parent::__construct($registry, $entityClass);
    }

    /**
     * @param $shipping
     * @param $service
     * @param $warehouse
     * @return GHNOrder
     */
    public function buildGHNOrder($shipping, $service, $warehouse): GHNOrder
    {
        /** @var GHNOrder $ghnOrder */
        $ghnOrder = $this->findOneBy(['Shipping' => $shipping]);
        if (!$ghnOrder || !$ghnOrder->isCreatedOrder()) {
            $ghnOrder = new GHNOrder();
            $ghnOrder->setShipping($shipping);
        }
        // set new service
        $ghnOrder->setGHNService($service);
        // set new warehouse
        $ghnOrder->setGHNWarehouse($warehouse);

        return $ghnOrder;
    }
}
<?php
/**
 * Author: lqdung1992@gmail.com
 * Date: 2/15/2019
 * Time: 4:27 PM
 */

namespace Plugin\GHNDelivery\Repository;


use Doctrine\Common\Persistence\ManagerRegistry;
use Eccube\Repository\AbstractRepository;
use Plugin\GHNDelivery\Entity\GHNService;

class GHNServiceRepository extends AbstractRepository
{
    /** @var GHNConfigRepository */
    protected $configRepo;

    public function __construct(ManagerRegistry $registry, GHNConfigRepository $configRepository)
    {
        parent::__construct($registry, GHNService::class);
        $this->configRepo = $configRepository;
    }

    /**
     * @param $shp
     * @param $mainServiceId
     * @param $warehouse
     * @return GHNService
     */
    public function buildGHNService($shp, $mainServiceId, $warehouse): GHNService
    {
        /** @var GHNService $service */
        $service = $this->findOneBy(['Shipping' => $shp]);
        if (!$service) {
            $service = new GHNService();
            $service->setShipping($shp);
        }
        $service->setMainServiceId($mainServiceId);

        // get service ID + fee
        $fromGHNPref = $warehouse->getGHNPref();
        $service->setFromPref($fromGHNPref);

        $toGHNPref = $shp->getGHNPref();
        $service->setToPref($toGHNPref);
        $service->setWeight($this->configRepo->find(1)->getWeight());

        return $service;
    }
}

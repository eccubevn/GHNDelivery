<?php
/**
 * Author: lqdung1992@gmail.com
 * Date: 2/14/2019
 * Time: 5:34 PM
 */

namespace Plugin\GHNDelivery\Service;


use Eccube\Common\EccubeConfig;
use Eccube\Entity\BaseInfo;
use Plugin\GHNDelivery\Entity\GHNWarehouse;
use Plugin\GHNDelivery\Repository\GHNConfigRepository;
use Plugin\GHNDelivery\Repository\GHNDeliveryRepository;
use Plugin\GHNDelivery\Repository\GHNPrefRepository;
use Plugin\GHNDelivery\Repository\GHNWarehouseRepository;

class ApiService
{
    /**
     * @var GHNConfigRepository
     */
    protected $configRepo;

    /**
     * @var GHNPrefRepository
     */
    protected $ghnPrefRepo;

    /**
     * @var GHNWarehouseRepository
     */
    protected $ghnWarehouseRepo;

    /**
     * @var GHNDeliveryRepository
     */
    protected $ghnDeliveryRepo;

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @var BaseInfo
     */
    protected $baseInfo;

    /**
     * ApiService constructor.
     * @param GHNConfigRepository $configRepo
     * @param GHNPrefRepository $ghnPrefRepo
     * @param GHNWarehouseRepository $ghnWarehouseRepo
     * @param GHNDeliveryRepository $ghnDeliveryRepo
     * @param EccubeConfig $eccubeConfig
     * @param BaseInfo $baseInfo
     */
    public function __construct(GHNConfigRepository $configRepo, GHNPrefRepository $ghnPrefRepo, GHNWarehouseRepository $ghnWarehouseRepo, GHNDeliveryRepository $ghnDeliveryRepo, EccubeConfig $eccubeConfig, BaseInfo $baseInfo)
    {
        $this->configRepo = $configRepo;
        $this->ghnPrefRepo = $ghnPrefRepo;
        $this->ghnWarehouseRepo = $ghnWarehouseRepo;
        $this->ghnDeliveryRepo = $ghnDeliveryRepo;
        $this->eccubeConfig = $eccubeConfig;
        $this->baseInfo = $baseInfo;
    }

    /**
     * @param GHNWarehouse $GHNWarehouse
     * @return string
     */
    public function addWarehouse(GHNWarehouse $GHNWarehouse)
    {
        return '';
    }
}
<?php

namespace Plugin\GHNDelivery\Controller\Admin;

use Eccube\Controller\AbstractController;
use Eccube\Entity\BaseInfo;
use Eccube\Repository\BaseInfoRepository;
use Plugin\GHNDelivery\Form\Type\Admin\ConfigType;
use Plugin\GHNDelivery\Form\Type\Admin\WarehouseType;
use Plugin\GHNDelivery\Repository\GHNConfigRepository;
use Plugin\GHNDelivery\Repository\GHNPrefRepository;
use Plugin\GHNDelivery\Repository\GHNWarehouseRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ConfigController extends AbstractController
{
    /**
     * @var GHNConfigRepository
     */
    protected $configRepository;

    /**
     * @var GHNPrefRepository
     */
    protected $GHNPrefRepo;

    /** @var BaseInfo */
    protected $BaseInfo;

    /**
     * @var GHNWarehouseRepository
     */
    protected $warehouseRepo;

    /**
     * ConfigController constructor.
     * @param GHNConfigRepository $configRepository
     * @param GHNPrefRepository $GHNPrefRepo
     */
    public function __construct(GHNConfigRepository $configRepository, GHNPrefRepository $GHNPrefRepo, BaseInfoRepository $baseInfoRepository, GHNWarehouseRepository $warehouseRepo)
    {
        $this->configRepository = $configRepository;
        $this->GHNPrefRepo = $GHNPrefRepo;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->warehouseRepo = $warehouseRepo;
    }


    /**
     * @Route("/%eccube_admin_route%/ghn_delivery/config", name="ghn_delivery_admin_config")
     * @Template("@GHNDelivery/admin/config.twig")
     */
    public function index(Request $request)
    {
        $Config = $this->configRepository->getOrCreate();
        $form = $this->createForm(ConfigType::class, $Config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $Config = $form->getData();
            $this->entityManager->persist($Config);
            $this->entityManager->flush($Config);
            $this->addSuccess('admin.common.save_complete', 'admin');

            return $this->redirectToRoute('ghn_delivery_admin_config');
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/ghn_delivery/warehouse", name="ghn_delivery_admin_warehouse")
     * @Template("@GHNDelivery/admin/warehouse.twig")
     */
    public function wareHouse(Request $request)
    {
        $Warehouse = $this->warehouseRepo->getByOne();
        $form = $this->createForm(WarehouseType::class, $Warehouse);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // call api register


            // save
        }

        return [
            'form' => $form->createView(),
        ];
    }
}

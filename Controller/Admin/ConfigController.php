<?php

namespace Plugin\GHNDelivery\Controller\Admin;

use Eccube\Controller\AbstractController;
use Eccube\Entity\BaseInfo;
use Eccube\Repository\BaseInfoRepository;
use Plugin\GHNDelivery\Entity\GHNConfig;
use Plugin\GHNDelivery\Form\Type\Admin\ConfigType;
use Plugin\GHNDelivery\Form\Type\Admin\WarehouseType;
use Plugin\GHNDelivery\Repository\GHNConfigRepository;
use Plugin\GHNDelivery\Repository\GHNPrefRepository;
use Plugin\GHNDelivery\Repository\GHNWarehouseRepository;
use Plugin\GHNDelivery\Service\ApiParserService;
use Plugin\GHNDelivery\Service\ApiService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ConfigController
 * @package Plugin\GHNDelivery\Controller\Admin
 */
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
     * @var ApiService
     */
    protected $apiService;

    /**
     * ConfigController constructor.
     * @param GHNConfigRepository $configRepository
     * @param GHNPrefRepository $GHNPrefRepo
     * @param BaseInfoRepository $baseInfoRepository
     * @param GHNWarehouseRepository $warehouseRepo
     * @param ApiService $apiService
     * @throws \Exception
     */
    public function __construct(GHNConfigRepository $configRepository, GHNPrefRepository $GHNPrefRepo, BaseInfoRepository $baseInfoRepository, GHNWarehouseRepository $warehouseRepo, ApiService $apiService)
    {
        $this->configRepository = $configRepository;
        $this->GHNPrefRepo = $GHNPrefRepo;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->warehouseRepo = $warehouseRepo;
        $this->apiService = $apiService;
    }

    /**
     * @Route("/%eccube_admin_route%/ghn/config", name="ghn_delivery_admin_config")
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
            if ($form['is_set_callback']->getData()) {
                // update config to server
                $output = $this->apiService->updateConfig($Config);
                if (!$output->getCode()) {
                    $this->addError($output->getMsg() ? $output->getMsg() : 'admin.common.save_error', 'admin');
                    $this->addPaserError($output);

                    return [
                        'form' => $form->createView(),
                    ];
                }
            }

            if (!$this->warehouseRepo->getOne()) {
                $this->addInfo('ghn.config.warehouse', 'admin');

                return $this->redirectToRoute('ghn_delivery_admin_warehouse');
            }

            return $this->redirectToRoute('ghn_delivery_admin_config');
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/ghn/warehouse", name="ghn_delivery_admin_warehouse")
     * @Template("@GHNDelivery/admin/warehouse.twig")
     */
    public function warehouse(Request $request)
    {
        /** @var GHNConfig $config */
        $config = $this->configRepository->find(1);
        if (!$config) {
            $this->addError('ghn.config.missing', 'admin');

            return $this->redirectToRoute('ghn_delivery_admin_config');
        }
        $Warehouse = $this->warehouseRepo->getOrCreate(true);
        $form = $this->createForm(WarehouseType::class, $Warehouse);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($Warehouse->getId()) {
                // call api update
                $parser = $this->apiService->updateWarehouse($Warehouse);
            } else {
                // call api register
                $parser = $this->apiService->addWarehouse($Warehouse);
            }
            if (!$parser) {
                $this->addError('admin.common.save_error', 'admin');
            } elseif ($parser->getCode()) {
                if (isset($parser->getData()['HubID'])) {
                    // save hub id
                    $Warehouse->setHubId($parser->getData()['HubID']);
                }
                $pref = $Warehouse->getGHNPref();
                $this->entityManager->persist($Warehouse);
                $pref->addWarehouse($Warehouse);
                $this->entityManager->flush();

                $this->addSuccess('admin.common.save_complete', 'admin');

                return $this->redirectToRoute('ghn_delivery_admin_warehouse');
            } else {
                $this->addError($parser->getMsg() ? $parser->getMsg() : 'admin.common.save_error', 'admin');
                $this->addPaserError($parser);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @param $output
     */
    private function addPaserError(ApiParserService $output): void
    {
        if (is_array($output->getData())) {
            foreach ($output->getData() as $message) {
                $this->addError($message, 'admin');
            }
        } elseif (is_string($output->getData())) {
            $this->addError($output->getData(), 'admin');
        }
    }
}

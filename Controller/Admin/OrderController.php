<?php
/**
 * Author: lqdung1992@gmail.com
 * Date: 2/21/2019
 * Time: 1:48 PM
 */

namespace Plugin\GHNDelivery\Controller\Admin;


use Eccube\Controller\AbstractController;
use Plugin\GHNDelivery\Entity\GHNPref;
use Plugin\GHNDelivery\Entity\GHNWarehouse;
use Plugin\GHNDelivery\Repository\GHNConfigRepository;
use Plugin\GHNDelivery\Repository\GHNPrefRepository;
use Plugin\GHNDelivery\Repository\GHNServiceRepository;
use Plugin\GHNDelivery\Repository\GHNWarehouseRepository;
use Plugin\GHNDelivery\Service\ApiService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    /**
     * @var GHNWarehouseRepository
     */
    protected $warehouseRepo;

    /**
     * @var ApiService
     */
    protected $apiService;

    /**
     * @var GHNServiceRepository
     */
    protected $serviceRepo;

    /**
     * @var GHNConfigRepository
     */
    protected $configRepo;

    /** @var GHNPrefRepository */
    protected $prefRepo;

    /**
     * OrderController constructor.
     * @param GHNWarehouseRepository $warehouseRepo
     * @param ApiService $apiService
     * @param GHNServiceRepository $serviceRepo
     * @param GHNConfigRepository $configRepo
     * @param GHNPrefRepository $prefRepo
     */
    public function __construct(GHNWarehouseRepository $warehouseRepo, ApiService $apiService, GHNServiceRepository $serviceRepo, GHNConfigRepository $configRepo, GHNPrefRepository $prefRepo)
    {
        $this->warehouseRepo = $warehouseRepo;
        $this->apiService = $apiService;
        $this->serviceRepo = $serviceRepo;
        $this->configRepo = $configRepo;
        $this->prefRepo = $prefRepo;
    }

    /**
     * ajax
     *
     * @param Request $request
     * @param int $id to district
     * @return array
     *
     * @Route(path="/%eccube_admin_route%/ghn/service/{id}", name="ghn_order_service_fee", requirements={"id"="\d+"}, methods={"POST"})
     * @Route(path="/%eccube_admin_route%/ghn/service/{id}/{index}", name="ghn_order_service_fee_index", requirements={"id"="\d+"}, methods={"POST"})
     * @Route(path="/%eccube_admin_route%/ghn/service", name="ghn_order_service_fee_no_id", methods={"POST"})
     * @Template("@GHNDelivery/admin/order_service_ajax.twig")
     */
    public function getServiceAndFee(Request $request, $id, $index = null)
    {
        if(!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        $this->isTokenValid();
        if (!is_numeric($id)) {
            throw new NotFoundHttpException();
        }

        /** @var GHNPref $GHNPref */
        $GHNPref = $this->prefRepo->find($id);
        if (!$GHNPref) {
            throw new NotFoundHttpException();
        }

        $data = [];
        $message = null;

        // from main warehouse
        /** @var GHNWarehouse $warehouse */
        $warehouse = $this->warehouseRepo->getOne();
        $fromDistrict = (int) $warehouse->getGHNPref()->getDistrictId();
        $toDistrict = (int) $GHNPref->getDistrictId();

        $isNeedCallApi = true;
        $sessionName = $this->eccubeConfig->get('admin_ghn_session_service_fee') . $index;
        if ($request->getSession()->has($sessionName)) {
            $isNeedCallApi = false;
            $data = $request->getSession()->get($sessionName);

            if ($fromDistrict != $data['from_id'] || $toDistrict != $data['to_id']) {
                $isNeedCallApi = true;
            }
        }

        if ($isNeedCallApi) {
            $weight = $this->configRepo->find(1)->getWeight();

            // call api to get service list
            $output = $this->apiService->findAvailableServices($fromDistrict, $toDistrict, ['Weight' => $weight]);
            $data = $output->getData();
            $message = $output->getMsg();
        }

        $data['from_id'] = $fromDistrict;
        $data['to_id'] = $toDistrict;

        $request->getSession()->set($sessionName, $data);

        return ['ghn_service' => $data, 'message' => $message, 'index' => $index];
    }
}
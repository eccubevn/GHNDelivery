<?php
/**
 * Author: lqdung1992@gmail.com
 * Date: 1/31/2019
 * Time: 2:42 PM
 */

namespace Plugin\GHNDelivery\Controller\Front\Shopping;


use Eccube\Controller\AbstractController;
use Eccube\Entity\Master\OrderItemType;
use Eccube\Entity\Master\TaxDisplayType;
use Eccube\Entity\Master\TaxType;
use Eccube\Entity\OrderItem;
use Eccube\Entity\Shipping;
use Eccube\Repository\Master\OrderItemTypeRepository;
use Plugin\GHNDelivery\Entity\GHNService;
use Plugin\GHNDelivery\Entity\GHNWarehouse;
use Plugin\GHNDelivery\Form\Type\Front\GHNDeliveryShoppingType;
use Plugin\GHNDelivery\Repository\GHNConfigRepository;
use Plugin\GHNDelivery\Repository\GHNServiceRepository;
use Plugin\GHNDelivery\Repository\GHNWarehouseRepository;
use Plugin\GHNDelivery\Service\ApiService;
use Plugin\GHNDelivery\Service\PurchaseFlow\GHNProcessor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GHNShoppingController extends AbstractController
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

    /**
     * @var OrderItemTypeRepository
     */
    protected $orderItemTypeRepository;

    /**
     * GHNShoppingController constructor.
     * @param GHNWarehouseRepository $warehouseRepo
     * @param ApiService $apiService
     * @param GHNServiceRepository $serviceRepo
     * @param GHNConfigRepository $configRepo
     * @param OrderItemTypeRepository $orderItemTypeRepository
     */
    public function __construct(GHNWarehouseRepository $warehouseRepo, ApiService $apiService, GHNServiceRepository $serviceRepo, GHNConfigRepository $configRepo, OrderItemTypeRepository $orderItemTypeRepository)
    {
        $this->warehouseRepo = $warehouseRepo;
        $this->apiService = $apiService;
        $this->serviceRepo = $serviceRepo;
        $this->configRepo = $configRepo;
        $this->orderItemTypeRepository = $orderItemTypeRepository;
    }

    /**
     * @param Request $request
     * @param Shipping $Shipping
     * @return array|RedirectResponse
     *
     * @Route(path="/shopping/ghn/delivery/{id}", name="ghn_delivery_shopping", requirements={"id"="\d+"})
     * @ParamConverter("Shipping")
     * @Template("@GHNDelivery/front/Shopping/delivery.twig")
     */
    public function index(Request $request, Shipping $Shipping)
    {
        $service = $this->serviceRepo->findOneBy(['Shipping' => $Shipping]);
        if (!$service) {
            $service = new GHNService();
            $service->setShipping($Shipping);
        }

        $builder = $this->formFactory->createBuilder(GHNDeliveryShoppingType::class, $Shipping, ['Pref' => $Shipping->getPref()]);

        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $service->setMainServiceId($form['main_service_id']->getData());
            // get service ID + fee
            $warehouse = $this->warehouseRepo->getOne();
            $fromGHNPref = $warehouse->getGHNPref();
            $service->setFromPref($fromGHNPref);

            $toGHNPref = $Shipping->getGHNPref();
            $service->setToPref($toGHNPref);

            $data = $request->getSession()->get($this->eccubeConfig->get('ghn_session_service_fee'));
            if (is_null($data)) {
                $output = $this->apiService->findAvailableServices($fromGHNPref->getDistrictId(), $toGHNPref->getDistrictId());
                // call api to get service list
                $data = $output->getData();
                // error
                if (empty($data) || !is_array($data)) {
                    $form['main_service_id']->addError(new FormError('ghn.shopping.delivery.service_connection'));

                    return [
                        'form' => $form->createView(),
                        'Shipping' => $Shipping,
                    ];
                }
            }

            if (!$service->setMainService($data)) {
                $form['main_service_id']->addError(new FormError('ghn.shopping.delivery.service_incorrect'));

                return [
                    'form' => $form->createView(),
                    'Shipping' => $Shipping,
                ];
            }

            $service->setWeight($this->configRepo->find(1)->getWeight());

            // remove old order item
            /** @var OrderItem $orderItem */
            foreach ($Shipping->getOrderItems() as $orderItem) {
                if ($orderItem->isDeliveryFee()) {
                    $Shipping->removeOrderItem($orderItem);
                    $Shipping->getOrder()->removeOrderItem($orderItem);
                    $this->entityManager->remove($orderItem);
                }
            }
            // add new to Order Item
            $type = $this->orderItemTypeRepository->find(OrderItemType::DELIVERY_FEE);
            $TaxInclude = $this->entityManager
                ->find(TaxDisplayType::class, TaxDisplayType::INCLUDED);
            $Taxation = $this->entityManager
                ->find(TaxType::class, TaxType::TAXATION);

            $OrderItem = new OrderItem();
            $OrderItem->setProductName(trans('ghn.delivery.order_item.name'))
                ->setPrice($service->getMainServiceFee())
                ->setQuantity(1)
                ->setOrderItemType($type)
                ->setShipping($Shipping)
                ->setOrder($Shipping->getOrder())
                ->setTaxDisplayType($TaxInclude)
                ->setTaxType($Taxation)
                ->setProcessorName(GHNProcessor::class);
            $this->entityManager->persist($OrderItem);
            $this->entityManager->flush($OrderItem);

            $service->setOrderItemId($OrderItem->getId());
            $this->entityManager->persist($service);

            $Shipping->addOrderItem($OrderItem);
            $Shipping->getOrder()->addItem($OrderItem);

            $this->entityManager->flush();

            // check still redirect?
            $redirects = $this->session->get($this->eccubeConfig->get('ghn_session_redirect'), [$Shipping->getId() => true]);
            unset($redirects[$Shipping->getId()]);
            $this->session->set($this->eccubeConfig->get('ghn_session_redirect'), $redirects);

            $nextValue = reset($redirects);
            if (!empty($redirects) && $nextValue) {
                $nextShippingId = key($redirects);

                return $this->redirectToRoute('ghn_delivery_shopping', ['id' => $nextShippingId]);
            }

            return $this->redirectToRoute('shopping');
        }

        return [
            'form' => $form->createView(),
            'Shipping' => $Shipping,
        ];
    }

    /**
     * ajax
     *
     * @param Request $request
     * @param Shipping $Shipping
     * @return array
     *
     * @Route(path="/shopping/ghn/service/{id}", name="ghn_delivery_service_fee", requirements={"id"="\d+"}, methods={"POST"})
     * @ParamConverter("Shipping")
     * @Template("@GHNDelivery/front/Shopping/service_ajax.twig")
     */
    public function getServiceAndFee(Request $request, Shipping $Shipping)
    {
        $this->isTokenValid();

        $builder = $this->formFactory->createBuilder(GHNDeliveryShoppingType::class, $Shipping, ['Pref' => $Shipping->getPref()]);
        $builder->remove('main_service_id');
        $form = $builder->getForm();
        $form->handleRequest($request);

        $data = [];
        $message = null;
        if ($form->isValid()) {
            // from main warehouse
            /** @var GHNWarehouse $warehouse */
            $warehouse = $this->warehouseRepo->getOne();
            $fromDistrict = (int) $warehouse->getGHNPref()->getDistrictId();
            $toDistrict = (int) $Shipping->getGHNPref()->getDistrictId();
            $weight = $this->configRepo->find(1)->getWeight();

            // call api to get service list
            $output = $this->apiService->findAvailableServices($fromDistrict, $toDistrict, ['Weight' => $weight]);
            $data = $output->getData();

            $request->getSession()->set($this->eccubeConfig->get('ghn_session_service_fee'), $data);
            $message = $output->getMsg();
        }

        return ['ghn_service' => $data, 'messge' => $message];
    }
}
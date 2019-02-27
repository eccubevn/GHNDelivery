<?php

namespace Plugin\GHNDelivery;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\Master\OrderStatus;
use Eccube\Entity\Order;
use Eccube\Entity\OrderItem;
use Eccube\Entity\Shipping;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Plugin\GHNDelivery\Entity\GHNConfig;
use Plugin\GHNDelivery\Entity\GHNOrder;
use Plugin\GHNDelivery\Entity\GHNService;
use Plugin\GHNDelivery\Repository\GHNConfigRepository;
use Plugin\GHNDelivery\Repository\GHNDeliveryRepository;
use Plugin\GHNDelivery\Repository\GHNOrderRepository;
use Plugin\GHNDelivery\Repository\GHNServiceRepository;
use Plugin\GHNDelivery\Repository\GHNWarehouseRepository;
use Plugin\GHNDelivery\Service\ApiService;
use Plugin\GHNDelivery\Service\PurchaseFlow\GHNProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

class Event implements EventSubscriberInterface
{
    use ControllerTrait;

    /** @var Session */
    protected $session;
    /** @var ContainerInterface */
    protected $container;
    /** @var EccubeConfig */
    protected $eccubeConfig;

    /** @var GHNDeliveryRepository */
    protected $ghnDeliveryRepo;

    /** @var ApiService */
    protected $apiService;

    /** @var GHNWarehouseRepository */
    protected $warehouseRepo;

    /** @var GHNOrderRepository */
    protected $ghnOrderRepo;

    /** @var GHNConfigRepository */
    protected $configRepo;

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var GHNServiceRepository */
    protected $serviceRepo;

    /**
     * @var RequestStack
     */
    protected $request;

    /**
     * Event constructor.
     * @param Session $session
     * @param ContainerInterface $container
     * @param EccubeConfig $eccubeConfig
     * @param GHNDeliveryRepository $GHNDeliveryRepo
     * @param ApiService $apiService
     * @param GHNWarehouseRepository $warehouseRepo
     * @param GHNOrderRepository $GHNOrderRepo
     * @param GHNConfigRepository $configRepo
     * @param EntityManagerInterface $entityManager
     * @param GHNServiceRepository $serviceRepo
     * @param RequestStack $request
     */
    public function __construct(Session $session, ContainerInterface $container, EccubeConfig $eccubeConfig, GHNDeliveryRepository $GHNDeliveryRepo, ApiService $apiService, GHNWarehouseRepository $warehouseRepo, GHNOrderRepository $GHNOrderRepo, GHNConfigRepository $configRepo, EntityManagerInterface $entityManager, GHNServiceRepository $serviceRepo, RequestStack $request)
    {
        $this->session = $session;
        $this->container = $container;
        $this->eccubeConfig = $eccubeConfig;
        $this->ghnDeliveryRepo = $GHNDeliveryRepo;
        $this->apiService = $apiService;
        $this->warehouseRepo = $warehouseRepo;
        $this->ghnOrderRepo = $GHNOrderRepo;
        $this->configRepo = $configRepo;
        $this->entityManager = $entityManager;
        $this->serviceRepo = $serviceRepo;
        $this->request = $request;
    }


    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopping/index.twig' => 'shoppingIndex',
            EccubeEvents::ADMIN_ORDER_EDIT_INDEX_COMPLETE => 'createGHNOrder',
            EccubeEvents::ADMIN_ORDER_UPDATE_STATUS_COMPLETE => 'createGHNOrderForOneShipping',
            EccubeEvents::ADMIN_ORDER_EDIT_INDEX_PROGRESS => 'calcGHNServeFeeOneShipping',
            EccubeEvents::ADMIN_SHIPPING_EDIT_INDEX_COMPLETE => 'calcGHNServeFeeMultiShipping',
            '@admin/Order/edit.twig' => 'renderOrder'
        ];
    }

    public function renderOrder(TemplateEvent $event)
    {
        $order = $event->getParameter('Order');
        $ghnOrder = $this->ghnOrderRepo->findBy(['Order' => $order]);
//        if (count($ghnOrder) == 0) {
//            return;
//        }
        $event->setParameter('GHNOrders', $ghnOrder);
        $event->addSnippet('@GHNDelivery/admin/render_order.twig');
    }

    public function calcGHNServeFeeMultiShipping(EventArgs $eventArgs)
    {
        $config = $this->configRepo->find(1);
        if (!$config) {
            return;
        }
        /** @var Shipping[] $targetShippings */
        $targetShippings = $eventArgs['TargetShippings'];

        $warehouse = $this->warehouseRepo->getOne();
        $request = $this->request->getCurrentRequest();
        $dataRequest = $request->get('form')['shippings'];

        foreach ($targetShippings as $key => $shipping) {
            $mainServiceId = $dataRequest[$key]['main_service_id'];
            $service = $this->serviceRepo->buildGHNService($shipping, $mainServiceId, $warehouse);

            $data = $this->session->get($this->eccubeConfig->get('admin_ghn_session_service_fee') . $key);
            if (is_null($data)) {
                $output = $this->apiService->findAvailableServices($service->getFromDistrictId(), $service->getToDistrictId());
                // call api to get service list
                $data = $output->getData();
                // error
                if (empty($data) || !is_array($data)) {
                    $this->addFlash('eccube.admin.error', 'ghn.shopping.delivery.service_connection');

                    return;
                }
            }

            if (!$service->setMainService($data)) {
                $this->addFlash('eccube.admin.error', 'ghn.shopping.delivery.service_incorrect');

                return;
            }

            $shipping->setGHNService($service);
            $this->entityManager->persist($service);
            // don't flush
        }
    }

    /**
     * For one shipping only
     *
     * @param EventArgs $eventArgs
     */
    public function calcGHNServeFeeOneShipping(EventArgs $eventArgs)
    {
        $config = $this->configRepo->find(1);
        if (!$config) {
            return;
        }
        /** @var Order $order */
        $order = $eventArgs['TargetOrder'];

        if ($order->isMultiple()) {
            return;
        }
        $warehouse = $this->warehouseRepo->getOne();

        $requestData = $this->request->getCurrentRequest()->get('order');
        if (!isset($requestData['Shipping']['main_service_id']) || !is_numeric($requestData['Shipping']['main_service_id'])) {
            $this->addFlash('eccube.admin.error', trans('ghn.shopping.delivery.service_incorrect'));
            return;
        }

        /** @var Shipping $shp */
        $shp = $order->getShippings()->first();
        $mainServiceId = $requestData['Shipping']['main_service_id'];
        $service = $this->serviceRepo->buildGHNService($shp, $mainServiceId, $warehouse);

        $data = $this->session->get($this->eccubeConfig->get('admin_ghn_session_service_fee'));
        if (is_null($data)) {
            $output = $this->apiService->findAvailableServices($service->getFromDistrictId(), $service->getToDistrictId());
            // call api to get service list
            $data = $output->getData();
            // error
            if (empty($data) || !is_array($data)) {
                $this->addFlash('eccube.admin.error', 'ghn.shopping.delivery.service_connection');

                return;
            }
        }

        if (!$service->setMainService($data)) {
            $this->addFlash('eccube.admin.error', 'ghn.shopping.delivery.service_incorrect');

            return;
        }
        $shp->setGHNService($service);
        $this->entityManager->persist($service);
        // do not flush
    }

    public function createGHNOrderForOneShipping(EventArgs $event)
    {
        /** @var GHNConfig $config */
        $config = $this->configRepo->find(1);
        if (!$config) {
            return;
        }
        /** @var Shipping $shipping */
        $shipping = $event['Shipping'];
        /** @var Order $order */
        $order = $shipping->getOrder();

        if ($order->getOrderStatus()->getId() != OrderStatus::IN_PROGRESS) {
            return;
        }

        $warehouse = $this->warehouseRepo->getOne();

        /** @var Shipping $shipping */
        $isGHNDelivery = $this->ghnDeliveryRepo->find($shipping->getDelivery());
        if (!$isGHNDelivery) {
            return;
        }

        $service = $shipping->getGHNService();
        if (!$service) {
            $this->session->getFlashBag()->add('eccube.admin.error', 'ghn.order.not_found');

            return;
        }

        $ghnOrder = $this->ghnOrderRepo->buildGHNOrder($shipping, $service, $warehouse);
        $output = $this->callApi($ghnOrder, $config);

        if (!$output->getCode()) {
            $this->session->getFlashBag()->add('eccube.admin.error', $output->getMsg() ? $output->getMsg() : 'ghn.order.can_not_create');

            return;
        }

        // save information
        $ghnOrder->setReturnData(serialize($output->getData()));
        $this->entityManager->persist($ghnOrder);
        // please flush all for update order
        $this->entityManager->flush();
    }

    public function createGHNOrder(EventArgs $event)
    {
        /** @var GHNConfig $config */
        $config = $this->configRepo->find(1);
        if (!$config) {
            return;
        }
        /** @var Order $order */
        $order = $event['TargetOrder'];

        if ($order->getOrderStatus()->getId() != OrderStatus::IN_PROGRESS) {
            return;
        }

        $warehouse = $this->warehouseRepo->getOne();

        /** @var Shipping $shipping */
        foreach ($order->getShippings() as $shipping) {
            $isGHNDelivery = $this->ghnDeliveryRepo->find($shipping->getDelivery());
            if (!$isGHNDelivery) {
                continue;
            }

            $service = $shipping->getGHNService();
            if (!$service) {
                $this->session->getFlashBag()->add('eccube.admin.error', 'ghn.order.not_found');

                return;
            }

            /** @var GHNOrder $ghnOrder */
            $ghnOrder = $this->ghnOrderRepo->buildGHNOrder($shipping, $service, $warehouse);
            $output = $this->callApi($ghnOrder, $config);

            if (!$output->getCode()) {
                $this->session->getFlashBag()->add('eccube.admin.error', 'ghn.order.can_not_create');

                return;
            }

            // save information
            $ghnOrder->setReturnData(serialize($output->getData()));
            $this->entityManager->persist($ghnOrder);
        }
        // please flush all for update order
        $this->entityManager->flush();
    }

    public function shoppingIndex(TemplateEvent $templateEvent)
    {
        $config = $this->configRepo->find(1);
        if (!$config) {
            return;
        }

        $parameters = $templateEvent->getParameters();
        /** @var Order $order */
        $order = $parameters['Order'];
        $redirects = [];
        foreach ($order->getShippings() as $shipping) {
            $redirects[$shipping->getId()] = false;
            $delivery = $shipping->getDelivery();
            $GHNDelivery = $this->ghnDeliveryRepo->find($delivery->getId());
            // if ghn delivery => add script to redirect to GHN page
            if ($GHNDelivery) {
                $redirects[$shipping->getId()] = true;
                /** @var OrderItem $shippingOrderItem */
                foreach ($shipping->getOrderItems() as $shippingOrderItem) {
                    // is exist GHN delivery fee
                    if ($shippingOrderItem->isDeliveryFee() && $shippingOrderItem->getProcessorName() == GHNProcessor::class) {
                        $redirects[$shipping->getId()] = false;
                    }
                }
            }
        }
        // save to session
        $this->session->set($this->eccubeConfig->get('ghn_session_redirect'), $redirects);

        foreach ($redirects as $key => $value) {
            if ($value) {
                $templateEvent->setResponse($this->redirectToRoute('ghn_delivery_shopping', ['id' => $key]));
                $templateEvent->setSource($templateEvent->getResponse()->getContent());
                break;
            }
        }
    }

    /**
     * @param $ghnOrder
     * @param $config
     * @return bool|Service\ApiParserService
     */
    private function callApi(GHNOrder $ghnOrder, $config)
    {
        // todo: condition?
        // update
        if ($ghnOrder->getId()) {
            $dataForApi = $ghnOrder->updateOrder($config);
            $output = $this->apiService->updateOrder($dataForApi);
        } else {
            // create
            $dataForApi = $ghnOrder->createOrder($config, $this->eccubeConfig->get('ghn_affiliate_id'));
            $output = $this->apiService->createOrder($dataForApi);
        }

        return $output;
    }
}

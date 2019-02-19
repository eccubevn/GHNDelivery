<?php

namespace Plugin\GHNDelivery;

use Eccube\Common\EccubeConfig;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Order;
use Eccube\Entity\OrderItem;
use Eccube\Event\TemplateEvent;
use Plugin\GHNDelivery\Repository\GHNDeliveryRepository;
use Plugin\GHNDelivery\Service\PurchaseFlow\GHNProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
    protected $GHNDeliveryRepo;

    /**
     * Event constructor.
     * @param Session $session
     * @param ContainerInterface $container
     * @param EccubeConfig $eccubeConfig
     * @param GHNDeliveryRepository $GHNDeliveryRepo
     */
    public function __construct(Session $session, ContainerInterface $container, EccubeConfig $eccubeConfig, GHNDeliveryRepository $GHNDeliveryRepo)
    {
        $this->session = $session;
        $this->container = $container;
        $this->eccubeConfig = $eccubeConfig;
        $this->GHNDeliveryRepo = $GHNDeliveryRepo;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            "Shopping/index.twig" => 'shoppingIndex'
        ];
    }

    public function shoppingIndex(TemplateEvent $templateEvent)
    {
        $parameters = $templateEvent->getParameters();
        /** @var Order $order */
        $order = $parameters['Order'];
        $redirects = [];
        foreach ($order->getShippings() as $shipping) {
            $redirects[$shipping->getId()] = false;
            $delivery = $shipping->getDelivery();
            $GHNDelivery = $this->GHNDeliveryRepo->find($delivery->getId());
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
}

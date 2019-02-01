<?php
/**
 * Author: lqdung1992@gmail.com
 * Date: 1/31/2019
 * Time: 2:42 PM
 */

namespace Plugin\GHNDelivery\Controller\Front\Shopping;


use Eccube\Controller\AbstractController;
use Eccube\Entity\Shipping;
use Plugin\GHNDelivery\Form\Type\Front\GHNDeliveryShoppingType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GHNShoppingController extends AbstractController
{
    /**
     * @param Request $request
     * @param Shipping $Shipping
     * @return array|RedirectResponse
     *
     * @Route(path="/shopping/ghn_delivery/{id}", name="ghn_delivery_shopping", requirements={"id"="\d+"})
     * @ParamConverter("Shipping")
     * @Template("@GHNDelivery/front/Shopping/delivery.twig")
     */
    public function index(Request $request, Shipping $Shipping)
    {
        $builder = $this->formFactory->createBuilder(GHNDeliveryShoppingType::class, $Shipping, ['Pref' => $Shipping->getPref()]);
        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $redirects = $this->session->get($this->eccubeConfig->get('ghn_session_name'), [$Shipping->getId() => true]);
            unset($redirects[$Shipping->getId()]);
            $this->session->set($this->eccubeConfig->get('ghn_session_name'), $redirects);

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
     * @return string
     *
     * @Route(path="/shopping/ghn_delivery/{id}", name="ghn_delivery_service_fee", requirements={"id"="\d+"})
     * @ParamConverter("Shipping")
     */
    public function getServiceFee(Request $request, Shipping $Shipping)
    {
        $this->isTokenValid();
        // from main warehouse
        // $fromDistrict = warehouse
        $builder = $this->formFactory->createBuilder(GHNDeliveryShoppingType::class, $Shipping, ['Pref' => $Shipping->getPref()]);
        $form = $builder->getForm();
        if ($form->isValid()) {
            $toDistrict = $Shipping->getGHNPref()->getDistrictId();
            // call api to get fee
            // add fee to new order item

        }

        return $this->json([]);
    }
}
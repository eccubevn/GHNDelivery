<?php

namespace Plugin\GHNDelivery\Controller\Admin;

use Eccube\Controller\AbstractController;
use Plugin\GHNDelivery\Form\Type\Admin\ConfigType;
use Plugin\GHNDelivery\Repository\GHNConfigRepository;
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
     * ConfigController constructor.
     *
     * @param GHNConfigRepository $configRepository
     */
    public function __construct(GHNConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
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
            $this->addSuccess('登録しました。', 'admin');

            return $this->redirectToRoute('ghn_delivery_admin_config');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}

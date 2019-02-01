<?php
/**
 * Author: lqdung1992@gmail.com
 * Date: 1/29/2019
 * Time: 2:52 PM
 */

namespace Plugin\GHNDelivery;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Entity\Delivery;
use Eccube\Entity\DeliveryFee;
use Eccube\Entity\Layout;
use Eccube\Entity\Master\SaleType;
use Eccube\Entity\Page;
use Eccube\Entity\PageLayout;
use Eccube\Entity\Payment;
use Eccube\Entity\PaymentOption;
use Eccube\Plugin\AbstractPluginManager;
use Eccube\Repository\Master\PrefRepository;
use Plugin\GHNDelivery\Entity\GHNConfig;
use Plugin\GHNDelivery\Entity\GHNDelivery;
use Plugin\GHNDelivery\Repository\GHNConfigRepository;
use Plugin\GHNDelivery\Repository\GHNDeliveryRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PluginManager
 * @package Plugin\GHNDelivery
 */
class PluginManager extends AbstractPluginManager
{
    /**
     * Install the plugin.
     *
     * @param array $meta
     * @param ContainerInterface $container
     */
    public function install(array $meta, ContainerInterface $container)
    {
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.orm.entity_manager');
        $this->loadFixturesGHNPref($em);
        $this->setupGHNDelivery($container, $em);
        $this->setupPageLayout($em);

        // flush all
        $em->flush();
    }

    /**
     * Update the plugin.
     *
     * @param array $meta
     * @param ContainerInterface $container
     */
    public function update(array $meta, ContainerInterface $container)
    {
    }

    /**
     * Enable the plugin.
     *
     * @param array $meta
     * @param ContainerInterface $container
     */
    public function enable(array $meta, ContainerInterface $container)
    {
    }

    /**
     * Disable the plugin.
     *
     * @param array $meta
     * @param ContainerInterface $container
     */
    public function disable(array $meta, ContainerInterface $container)
    {
    }

    /**
     * Uninstall the plugin.
     *
     * @param array $meta
     * @param ContainerInterface $container
     */
    public function uninstall(array $meta, ContainerInterface $container)
    {
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.orm.entity_manager');
        $configRepo = $container->get(GHNDeliveryRepository::class);
        /** @var GHNDelivery[] $config */
        $config = $configRepo->findAll();
        foreach ($config as $GHNDelivery) {
            foreach ($GHNDelivery->getDelivery()->getDeliveryFees() as $deliveryFee) {
                $em->remove($deliveryFee);
            }
            $delivery = $GHNDelivery->getDelivery();
            $delivery->setVisible(false);
            $delivery->setName(trans('ghn.plugin_manager.delivery_name.remove'));
            $em->remove($GHNDelivery);
            $em->persist($delivery);
        }
        $em->flush();
    }

    /**
     * @param $em
     */
    private function loadFixturesGHNPref($em): void
    {
        // setup giao hang nhanh province
        $loader = new \Eccube\Doctrine\Common\CsvDataFixtures\Loader();
        $loader->loadFromDirectory(__DIR__ . '/Resource/doctrine/import_csv/');
        $executor = new \Eccube\Doctrine\Common\CsvDataFixtures\Executor\DbalExecutor($em);
        $fixtures = $loader->getFixtures();
        $executor->execute($fixtures);
    }

    /**
     * @param ContainerInterface $container
     * @param $em
     */
    private function setupGHNDelivery(ContainerInterface $container, EntityManagerInterface $em): void
    {
        // setup GHN delivery
        $saleType = $em->getRepository(SaleType::class)->find(SaleType::SALE_TYPE_NORMAL);
        $paymentMethods = $em->getRepository(Payment::class)->findAll();
        $delivery = new Delivery();
        $delivery->setName(trans('ghn.plugin_manager.delivery_name'))
            ->setSaleType($saleType)
            ->setServiceName(trans('ghn.plugin_manager.service_name'))
            ->setVisible(true)
            ->setConfirmUrl(trans('ghn.plugin_manager.delivery_url'));
        $em->persist($delivery);
        foreach ($paymentMethods as $paymentMethod) {
            $method = new PaymentOption();
            $method->setDelivery($delivery)
                ->setPayment($paymentMethod);
            $em->persist($method);
        }

        // save delivery id
        $configDelivery = new GHNDelivery();
        $configDelivery->setDelivery($delivery);
        $em->persist($configDelivery);

        // setup delivery fee - all zero
        $prefRepo = $container->get(PrefRepository::class);
        $allPrefs = $prefRepo->findAll();
        foreach ($allPrefs as $pref) {
            $GHNFee = new DeliveryFee();
            $GHNFee->setPref($pref)
                ->setDelivery($delivery)
                ->setFee(0);
            $em->persist($GHNFee);
        }
    }

    /**
     * @param $em
     */
    private function setupPageLayout(EntityManagerInterface $em): void
    {
        // add to layout
        $page = new Page();
        $page->setName(trans('ghn.plugin_manager.page_name'))
            ->setUrl('ghn_delivery_shopping')
            ->setFileName("@GHNDelivery/front/Shopping/delivery.twig")
            ->setEditType(Page::EDIT_TYPE_DEFAULT);
        $em->persist($page);
        $layout = $em->getRepository(Layout::class)->find(Layout::DEFAULT_LAYOUT_UNDERLAYER_PAGE);
        $pageLayout = new PageLayout();
        $pageLayout->setPage($page)
            ->setLayout($layout);
        $page->addPageLayout($pageLayout);
        $em->persist($pageLayout);
    }
}
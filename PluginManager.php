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
use Eccube\Entity\Master\SaleType;
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

        // setup giao hang nhanh province
        $loader = new \Eccube\Doctrine\Common\CsvDataFixtures\Loader();
        $loader->loadFromDirectory(__DIR__.'/Resource/doctrine/import_csv/');
        $executer = new \Eccube\Doctrine\Common\CsvDataFixtures\Executor\DbalExecutor($em);
        $fixtures = $loader->getFixtures();
        $executer->execute($fixtures);

        // setup GHN delivery
        $saleType = $em->getRepository(SaleType::class)->find(SaleType::SALE_TYPE_NORMAL);
        $delivery = new Delivery();
        $delivery->setName("Giao hàng nhanh")
            ->setSaleType($saleType)
            ->setServiceName('GHN')
            ->setVisible(true)
            ->setConfirmUrl('https://giaohangnhanh.vn/');
        $em->persist($delivery);
        $em->flush($delivery);

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
            $em->remove($GHNDelivery->getDelivery());
            $em->remove($GHNDelivery);
        }
        $em->flush();
    }
}
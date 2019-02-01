<?php
/**
 * Author: lqdung1992@gmail.com
 * Date: 1/31/2019
 * Time: 2:56 PM
 */

namespace Plugin\GHNDelivery\Form\Type\Front;

use Doctrine\ORM\EntityRepository;
use Eccube\Entity\Shipping;
use Plugin\GHNDelivery\Entity\GHNPref;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GHNDeliveryShoppingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** Master Pref ID of eccube $Pref */
        $Pref = $options['Pref'];

        $builder->add('gHNPref', EntityType::class, [
            'label' => 'ghn.shopping.delivery.district',
            'class' => GHNPref::class,
            'choice_label' => 'district_name',
            'query_builder' => function (EntityRepository $entityRepository) use ($Pref) {
                return $entityRepository->createQueryBuilder('ghn_pref')
                    ->where('ghn_pref.Pref = :pref')
                    ->setParameter('pref', $Pref)
                    ->orderBy('ghn_pref.district_name', ' DESC');
            },
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('Pref');
        $resolver->setDefaults(['data_class' => Shipping::class]);
    }

    public function getBlockPrefix()
    {
        return parent::getBlockPrefix();
    }
}

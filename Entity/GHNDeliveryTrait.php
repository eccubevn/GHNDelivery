<?php
/**
 * Author: lqdung1992@gmail.com
 * Date: 1/29/2019
 * Time: 4:26 PM
 */

namespace Plugin\GHNDelivery\Entity;

use Eccube\Annotation as Eccube;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Delivery;

/**
 * @Eccube\EntityExtension("Eccube\Entity\Delivery")
 */
trait GHNDeliveryTrait
{
    /**
     * @var ?GHNDelivery
     *
     * @ORM\OneToMany(targetEntity="Plugin\GHNDelivery\Entity\GHNDelivery", mappedBy="Delivery")
     */
    private $GHNDelivery;

    /**
     * @return mixed
     */
    public function getGHNDelivery(): ?GHNDelivery
    {
        return $this->GHNDelivery;
    }

    /**
     * @param Delivery
     */
    public function setGHNDelivery(GHNDelivery $GHNDelivery)
    {
        $this->GHNDelivery = $GHNDelivery;

        return $this;
    }
}
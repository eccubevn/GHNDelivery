<?php
/**
 * Author: lqdung1992@gmail.com
 * Date: 2/21/2019
 * Time: 11:22 AM
 */

namespace Plugin\GHNDelivery\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation as Eccube;

/**
 * Trait DeliveryTrait
 * @package Plugin\GHNDelivery\Entity
 *
 * @Eccube\EntityExtension("Eccube\Entity\Delivery")
 */
trait DeliveryTrait
{
    /**
     * @var GHNDelivery
     *
     * @ORM\OneToOne(targetEntity="Plugin\GHNDelivery\Entity\GHNDelivery")
     */
    private $GHNDelivery;

    /**
     * @return GHNDelivery
     */
    public function getGHNDelivery()
    {
        return $this->GHNDelivery;
    }

    /**
     * @param GHNDelivery $GHNDelivery
     * @return $this
     */
    public function setGHNDelivery(GHNDelivery $GHNDelivery)
    {
        $this->GHNDelivery = $GHNDelivery;
        return $this;
    }

    public function isGHNDelivery()
    {
        return (empty($this->GHNDelivery) ? false : true);
    }
}

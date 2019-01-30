<?php
/**
 * Author: lqdung1992@gmail.com
 * Date: 1/30/2019
 * Time: 2:27 PM
 */

namespace Plugin\GHNDelivery\Entity;


use Eccube\Entity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Delivery;

/**
 * Class GHNDelivery
 * @package Plugin\GHNDelivery\Entity
 *
 * @ORM\Table(name="plg_ghn_delivery")
 * @ORM\Entity(repositoryClass="Plugin\GHNDelivery\Repository\GHNDeliveryRepository")
 */
class GHNDelivery extends AbstractEntity
{
    /**
     * @var Delivery
     *
     * @ORM\OneToOne(targetEntity="Eccube\Entity\Delivery", inversedBy="GHNDelivery")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="delivery_id", referencedColumnName="id")
     * })
     * @ORM\Id
     */
    private $Delivery;

    /**
     * @return Delivery
     */
    public function getDelivery()
    {
        return $this->Delivery;
    }

    /**
     * @param Delivery $Delivery
     */
    public function setDelivery(Delivery $Delivery)
    {
        $this->Delivery = $Delivery;
    }
}

<?php
/**
 * Author: lqdung1992@gmail.com
 * Date: 1/29/2019
 * Time: 4:26 PM
 */

namespace Plugin\GHNDelivery\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Annotation as Eccube;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Eccube\EntityExtension("Eccube\Entity\Master\Pref")
 */
trait GHNPrefTrait
{
    public function __construct()
    {
        $this->GHNPrefs = new ArrayCollection();
    }

    /**
     * @var GHNPref[]
     *
     * @ORM\OneToMany(targetEntity="Plugin\GHNDelivery\Entity\GHNPref", mappedBy="Pref")
     */
    private $GHNPrefs;

    /**
     * @return GHNPref[]
     */
    public function getGHNPrefs()
    {
        return $this->GHNPrefs;
    }

    /**
     * @param GHNPref $GHNPrefs
     */
    public function addGHNPref(GHNPref $GHNPref)
    {
        $this->GHNPrefs->add($GHNPref);

        return $this;
    }

    /**
     * @param GHNPref $GHNPrefs
     */
    public function removeGHNPref(GHNPref $GHNPref)
    {
        $this->GHNPrefs->removeElement($GHNPref);

        return $this;
    }
}
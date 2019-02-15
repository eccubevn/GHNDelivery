<?php
/**
 * Author: lqdung1992@gmail.com
 * Date: 1/29/2019
 * Time: 3:31 PM
 */

namespace Plugin\GHNDelivery\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Entity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Master\Pref;

/**
 * Class GHNPref
 * @package Plugin\GHNDelivery\Entity
 * @ORM\Table(name="plg_ghn_pref")
 * @ORM\Entity(repositoryClass="Plugin\GHNDelivery\Repository\GHNPrefRepository")
 */
class GHNPref extends AbstractEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="province_name", type="string", length=255)
     */
    private $province_name;

    /**
     * @var string
     *
     * @ORM\Column(name="province_id", type="integer")
     */
    private $province_id;

    /**
     * @var string
     *
     * @ORM\Column(name="district_name", type="string", length=255)
     */
    private $district_name;

    /**
     * @var string
     *
     * @ORM\Column(name="district_code", type="string", length=255)
     */
    private $district_code;

    /**
     * @var string
     *
     * @ORM\Column(name="district_id", type="integer")
     */
    private $district_id;

    /**
     * @var Pref
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\Pref")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pref_eccube_id", referencedColumnName="id")
     * })
     */
    private $Pref;

    /**
     * @var GHNWarehouse[]
     *
     * @ORM\OneToMany(targetEntity="Plugin\GHNDelivery\Entity\GHNWarehouse", mappedBy="GHNPref")
     */
    private $Warehouses;

    /**
     * GHNPref constructor.
     */
    public function __construct()
    {
        $this->Warehouses = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getProvinceName(): string
    {
        return $this->province_name;
    }

    /**
     * @param string $province_name
     */
    public function setProvinceName(string $province_name): void
    {
        $this->province_name = $province_name;
    }

    /**
     * @return string
     */
    public function getProvinceId(): string
    {
        return $this->province_id;
    }

    /**
     * @param string $province_id
     */
    public function setProvinceId(string $province_id): void
    {
        $this->province_id = $province_id;
    }

    /**
     * @return string
     */
    public function getDistrictName(): string
    {
        return $this->district_name;
    }

    /**
     * @param string $district_name
     */
    public function setDistrictName(string $district_name): void
    {
        $this->district_name = $district_name;
    }

    /**
     * @return string
     */
    public function getDistrictCode(): string
    {
        return $this->district_code;
    }

    /**
     * @param string $district_code
     */
    public function setDistrictCode(string $district_code): void
    {
        $this->district_code = $district_code;
    }

    /**
     * @return string
     */
    public function getDistrictId(): string
    {
        return $this->district_id;
    }

    /**
     * @param string $district_id
     */
    public function setDistrictId(string $district_id): void
    {
        $this->district_id = $district_id;
    }

    /**
     * @return Pref
     */
    public function getPref(): Pref
    {
        return $this->Pref;
    }

    /**
     * @param Pref $Pref
     */
    public function setPref(Pref $Pref): void
    {
        $this->Pref = $Pref;
    }

    /**
     * @return GHNWarehouse[]
     */
    public function getWarehouses()
    {
        return $this->Warehouses;
    }

    /**
     * @param GHNWarehouse $Warehouse
     */
    public function addWarehouse(GHNWarehouse $Warehouse): void
    {
        if (is_null($this->Warehouses)) {
            $this->Warehouses = new ArrayCollection();
        }

        $this->Warehouses->add($Warehouse);
    }

    /**
     * @param GHNWarehouse $Warehouse
     */
    public function removeWarehouse($Warehouse): void
    {
        $this->Warehouses->removeElement($Warehouse);
    }
}

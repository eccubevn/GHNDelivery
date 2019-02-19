<?php
/**
 * Author: lqdung1992@gmail.com
 * Date: 2/14/2019
 * Time: 4:58 PM
 */

namespace Plugin\GHNDelivery\Entity;

use Eccube\Entity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Warehouse
 *
 * @package Plugin\GHNDelivery\Entity
 *
 * @ORM\Table(name="plg_ghn_warehouse")
 * @ORM\Entity(repositoryClass="Plugin\GHNDelivery\Repository\GHNWarehouseRepository")
 */
class GHNWarehouse extends AbstractEntity
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
     * @var string|null
     *
     * @ORM\Column(name="address", type="string", length=255)
     */
    private $address;

    /**
     * @var string|null
     *
     * @ORM\Column(name="contact_name", type="string", length=255)
     */
    private $contact_name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="contact_phone", type="string", length=30)
     */
    private $contact_phone;

    /**
     * @var GHNPref
     *
     * @ORM\ManyToOne(targetEntity="Plugin\GHNDelivery\Entity\GHNPref", inversedBy="Warehouses")
     * @ORM\JoinColumns(
     *     @ORM\JoinColumn(name="ghn_pref_id", referencedColumnName="id")
     * )
     */
    private $GHNPref;

    /**
     * @var string|null
     *
     * @ORM\Column(name="longitude", type="string", length=255, nullable=true)
     */
    private $long;

    /**
     * @var string|null
     *
     * @ORM\Column(name="latitude", type="string", length=255, nullable=true)
     */
    private $lati;

    /**
     * @var string|null
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(name="is_main", type="boolean", nullable=true)
     */
    private $is_main;

    /**
     * @var int
     *
     * @ORM\Column(name="hub_id", type="integer", nullable=true)
     */
    private $hub_id;

    /**
     * GHNWarehouse constructor.
     * @param int $hub_id
     */
    public function __construct()
    {
        $this->is_main = true;
    }


    /**
     * @return int
     */
    public function getId()
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
     * @return null|string
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param null|string $address
     */
    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    /**
     * @return null|string
     */
    public function getContactName(): ?string
    {
        return $this->contact_name;
    }

    /**
     * @param null|string $contact_name
     */
    public function setContactName(?string $contact_name): void
    {
        $this->contact_name = $contact_name;
    }

    /**
     * @return null|string
     */
    public function getContactPhone(): ?string
    {
        return $this->contact_phone;
    }

    /**
     * @param null|string $contact_phone
     */
    public function setContactPhone(?string $contact_phone): void
    {
        $this->contact_phone = $contact_phone;
    }

    /**
     * @return GHNPref
     */
    public function getGHNPref(): ?GHNPref
    {
        return $this->GHNPref;
    }

    /**
     * @param GHNPref $GHNPref
     */
    public function setGHNPref(GHNPref $GHNPref): void
    {
        $this->GHNPref = $GHNPref;
    }

    /**
     * @return null|string
     */
    public function getLong(): ?string
    {
        return $this->long;
    }

    /**
     * @param null|string $long
     */
    public function setLong(?string $long): void
    {
        $this->long = $long;
    }

    /**
     * @return null|string
     */
    public function getLati(): ?string
    {
        return $this->lati;
    }

    /**
     * @param null|string $lati
     */
    public function setLati(?string $lati): void
    {
        $this->lati = $lati;
    }

    /**
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param null|string $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return null|string
     */
    public function getisMain(): ?string
    {
        return $this->is_main;
    }

    /**
     * @param null|string $is_main
     */
    public function setIsMain(?string $is_main): void
    {
        $this->is_main = $is_main;
    }

    /**
     * @return int
     */
    public function getHubId()
    {
        return $this->hub_id;
    }

    /**
     * @param int $hub_id
     */
    public function setHubId(int $hub_id): void
    {
        $this->hub_id = $hub_id;
    }

    public function getApiCreateParameter()
    {
        $ret = [
            'Address' => $this->getAddress(),
            'ContactName' => $this->getContactName(),
            'ContactPhone' => $this->getContactPhone(),
            'DistrictID' => $this->getGHNPref() ? (int) $this->getGHNPref()->getDistrictId() : null,
            'IsMain' => true, // tmp
        ];

        if ($this->getEmail()) {
            $ret['Email'] = $this->getEmail();
        }

        if ($this->getLati()) {
            $ret['Latitude'] = $this->getLati();
        }

        if ($this->getLong()) {
            $ret['Longitude'] = $this->getLong();
        }

        return $ret;
    }
}
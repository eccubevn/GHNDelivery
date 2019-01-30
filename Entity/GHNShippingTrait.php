<?php
/**
 * Author: lqdung1992@gmail.com
 * Date: 1/28/2019
 * Time: 2:29 PM
 */

namespace Plugin\GHNDelivery\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Todo continue (ver 2.0 - fee)
 *
 * Trait GHNShippingTrait
 * @package Plugin\GHNDelivery\Entity
 */
trait GHNShippingTrait
{
    /**
     * Allowed values: CHOTHUHANG, CHOXEMHANGKHONGTHU, KHONGCHOXEMHANG
     *
     * @var string
     *
     * @ORM\Column(name="note_code", type="string", length=255, nullable=true)
     */
    private $note_code;

    /**
     * @var float
     *
     * @ORM\Column(name="insurance_fee", type="float", precision=0)
     */
    private $insurance_fee = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="note", nullable=true, type="string", length=3000)
     */
    private $note;

    /**
     * @var string
     *
     * @ORM\Column(name="coupon_code", type="string", length=30, nullable=true)
     */
    private $coupon_code;

    /**
     * @return string
     */
    public function getNoteCode(): string
    {
        return $this->note_code;
    }

    /**
     * @param string $note_code
     */
    public function setNoteCode(string $note_code): void
    {
    }

    /**
     * @return float
     */
    public function getInsuranceFee(): float
    {
        return $this->insurance_fee;
    }

    /**
     * @param float $insurance_fee
     */
    public function setInsuranceFee(float $insurance_fee): void
    {
        $this->insurance_fee = $insurance_fee;
    }

    /**
     * @return string
     */
    public function getNote(): string
    {
        return $this->note;
    }

    /**
     * @param string $note
     */
    public function setNote(string $note): void
    {
        $this->note = $note;
    }

    /**
     * @return string
     */
    public function getCouponCode(): string
    {
        return $this->coupon_code;
    }

    /**
     * @param string $coupon_code
     */
    public function setCouponCode(string $coupon_code): void
    {
        $this->coupon_code = $coupon_code;
    }
}

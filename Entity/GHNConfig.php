<?php

namespace Plugin\GHNDelivery\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

/**
 * Config
 *
 * @ORM\Table(name="plg_ghn_delivery_config")
 * @ORM\Entity(repositoryClass="Plugin\GHNDelivery\Repository\GHNConfigRepository")
 */
class GHNConfig extends AbstractEntity
{
    const PAYMENT_TYPE_SELLER = 1;
    CONST PAYMENT_TYPE_BUYER = 2;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="client_id", type="string", length=255)
     */
    private $client_id;

    /**
     * @var string
     *
     * @ORM\Column(name="client_name", type="string", length=255, nullable=true)
     */
    private $client_name;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255)
     */
    private $token;

    /**
     * @var float
     *
     * @ORM\Column(name="weight", type="float", precision=1)
     */
    private $weight;

    /**
     * Choose who pay shipping fee.
     * 1: Shop/Seller.
     * 2: Buyer/Consignee.
     * 4: GHN Wallet.
     * 5: Credit.
     * Default value: 1 or depend on each specific account
     * Allowed values: 1, 2
     *
     * @var int
     *
     * @ORM\Column(name="payment_type", type="smallint")
     */
    private $payment_type = 1;

    /**
     * @var string
     *
     * @ORM\Column(name="callback_url", type="string", length=2000)
     */
    private $callback_url;

    /**
     * Allowed values: CHOTHUHANG, CHOXEMHANGKHONGTHU, KHONGCHOXEMHANG
     *
     * @var string
     *
     * @ORM\Column(name="note_code", type="string", length=255)
     */
    private $note_code;

    /**
     * @var float
     *
     * @ORM\Column(name="insurance_fee", type="float", precision=0)
     */
    private $insurance_fee = 0;

    /**
     * Check if this account have input bank account information. GHN use bank account to transfer COD.
     *
     * @var bool
     *
     * @ORM\Column(name="check_main_bank_account", type="boolean")
     */
    private $check_main_bank_account = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_credit_create", type="boolean")
     */
    private $is_credit_create = false;

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
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * @param string $client_id
     */
    public function setClientId(string $client_id)
    {
        $this->client_id = $client_id;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientName()
    {
        return $this->client_name;
    }

    /**
     * @param string $client_name
     */
    public function setClientName($client_name)
    {
        $this->client_name = $client_name;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param float $weight
     */
    public function setWeight(float $weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @return string
     */
    public function getCallbackUrl()
    {
        return $this->callback_url;
    }

    /**
     * @param string $callback_url
     */
    public function setCallbackUrl(string $callback_url)
    {
        $this->callback_url = $callback_url;

        return $this;
    }

    /**
     * @return string
     */
    public function getNoteCode()
    {
        return $this->note_code;
    }

    /**
     * @param string $note_code
     */
    public function setNoteCode(string $note_code)
    {
        $this->note_code = $note_code;
    }

    /**
     * @return float
     */
    public function getInsuranceFee()
    {
        return $this->insurance_fee;
    }

    /**
     * @param float $insurance_fee
     */
    public function setInsuranceFee(float $insurance_fee)
    {
        $this->insurance_fee = $insurance_fee;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCheckMainBankAccount(): bool
    {
        return $this->check_main_bank_account;
    }

    /**
     * @param bool $check_main_bank_account
     */
    public function setCheckMainBankAccount(bool $check_main_bank_account)
    {
        $this->check_main_bank_account = $check_main_bank_account;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCreditCreate()
    {
        return $this->is_credit_create;
    }

    /**
     * @param bool $is_credit_create
     */
    public function setIsCreditCreate(bool $is_credit_create)
    {
        $this->is_credit_create = $is_credit_create;

        return $this;
    }

    /**
     * @return int
     */
    public function getPaymentType()
    {
        return $this->payment_type;
    }

    /**
     * @param int $payment_type
     */
    public function setPaymentType(int $payment_type)
    {
        $this->payment_type = $payment_type;

        return $this;
    }
}

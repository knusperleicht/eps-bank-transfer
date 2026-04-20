<?php

namespace Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V27\TransferInitiatorDetails;

/**
 * Class representing TransferInitiatorDetailsAType
 */
class TransferInitiatorDetailsAType
{
    /**
     * @var \Knusperleicht\EpsBankTransfer\Internal\Generated\Payment\V27\PaymentInitiatorDetails $paymentInitiatorDetails
     */
    private $paymentInitiatorDetails = null;

    /**
     * @var \Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V27\TransferMsgDetails $transferMsgDetails
     */
    private $transferMsgDetails = null;

    /**
     * @var \Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V27\WebshopArticle[] $webshopDetails
     */
    private $webshopDetails = null;

    /**
     * @var string $transactionId
     */
    private $transactionId = null;

    /**
     * @var string $qRCodeUrl
     */
    private $qRCodeUrl = null;

    /**
     * @var \Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V27\AuthenticationDetails $authenticationDetails
     */
    private $authenticationDetails = null;

    /**
     * Gets as paymentInitiatorDetails
     *
     * @return \Knusperleicht\EpsBankTransfer\Internal\Generated\Payment\V27\PaymentInitiatorDetails
     */
    public function getPaymentInitiatorDetails()
    {
        return $this->paymentInitiatorDetails;
    }

    /**
     * Sets a new paymentInitiatorDetails
     *
     * @param \Knusperleicht\EpsBankTransfer\Internal\Generated\Payment\V27\PaymentInitiatorDetails $paymentInitiatorDetails
     * @return self
     */
    public function setPaymentInitiatorDetails(\Knusperleicht\EpsBankTransfer\Internal\Generated\Payment\V27\PaymentInitiatorDetails $paymentInitiatorDetails)
    {
        $this->paymentInitiatorDetails = $paymentInitiatorDetails;
        return $this;
    }

    /**
     * Gets as transferMsgDetails
     *
     * @return \Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V27\TransferMsgDetails
     */
    public function getTransferMsgDetails()
    {
        return $this->transferMsgDetails;
    }

    /**
     * Sets a new transferMsgDetails
     *
     * @param \Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V27\TransferMsgDetails $transferMsgDetails
     * @return self
     */
    public function setTransferMsgDetails(\Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V27\TransferMsgDetails $transferMsgDetails)
    {
        $this->transferMsgDetails = $transferMsgDetails;
        return $this;
    }

    /**
     * Adds as webshopArticle
     *
     * @return self
     * @param \Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V27\WebshopArticle $webshopArticle
     */
    public function addToWebshopDetails(\Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V27\WebshopArticle $webshopArticle)
    {
        $this->webshopDetails[] = $webshopArticle;
        return $this;
    }

    /**
     * isset webshopDetails
     *
     * @param int|string $index
     * @return bool
     */
    public function issetWebshopDetails($index)
    {
        return isset($this->webshopDetails[$index]);
    }

    /**
     * unset webshopDetails
     *
     * @param int|string $index
     * @return void
     */
    public function unsetWebshopDetails($index)
    {
        unset($this->webshopDetails[$index]);
    }

    /**
     * Gets as webshopDetails
     *
     * @return \Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V27\WebshopArticle[]
     */
    public function getWebshopDetails()
    {
        return $this->webshopDetails;
    }

    /**
     * Sets a new webshopDetails
     *
     * @param \Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V27\WebshopArticle[] $webshopDetails
     * @return self
     */
    public function setWebshopDetails(array $webshopDetails = null)
    {
        $this->webshopDetails = $webshopDetails;
        return $this;
    }

    /**
     * Gets as transactionId
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * Sets a new transactionId
     *
     * @param string $transactionId
     * @return self
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    /**
     * Gets as qRCodeUrl
     *
     * @return string
     */
    public function getQRCodeUrl()
    {
        return $this->qRCodeUrl;
    }

    /**
     * Sets a new qRCodeUrl
     *
     * @param string $qRCodeUrl
     * @return self
     */
    public function setQRCodeUrl($qRCodeUrl)
    {
        $this->qRCodeUrl = $qRCodeUrl;
        return $this;
    }

    /**
     * Gets as authenticationDetails
     *
     * @return \Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V27\AuthenticationDetails
     */
    public function getAuthenticationDetails()
    {
        return $this->authenticationDetails;
    }

    /**
     * Sets a new authenticationDetails
     *
     * @param \Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V27\AuthenticationDetails $authenticationDetails
     * @return self
     */
    public function setAuthenticationDetails(\Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V27\AuthenticationDetails $authenticationDetails)
    {
        $this->authenticationDetails = $authenticationDetails;
        return $this;
    }
}


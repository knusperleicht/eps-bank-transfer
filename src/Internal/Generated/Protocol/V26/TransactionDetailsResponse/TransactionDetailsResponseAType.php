<?php

namespace Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V26\TransactionDetailsResponse;

/**
 * Class representing TransactionDetailsResponseAType
 */
class TransactionDetailsResponseAType
{
    /**
     * @var \Knusperleicht\EpsBankTransfer\Internal\Generated\Payment\V26\PaymentInitiatorDetails $paymentInitiatorDetails
     */
    private $paymentInitiatorDetails = null;

    /**
     * @var \Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V26\TransferMsgDetails $transferMsgDetails
     */
    private $transferMsgDetails = null;

    /**
     * @var \Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V26\WebshopArticle[] $webshopDetails
     */
    private $webshopDetails = null;

    /**
     * @var \Knusperleicht\EpsBankTransfer\Internal\Generated\XmlDsig\Signature $signature
     */
    private $signature = null;

    /**
     * @var \Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V26\ErrorDetails $errorDetails
     */
    private $errorDetails = null;

    /**
     * Gets as paymentInitiatorDetails
     *
     * @return \Knusperleicht\EpsBankTransfer\Internal\Generated\Payment\V26\PaymentInitiatorDetails
     */
    public function getPaymentInitiatorDetails()
    {
        return $this->paymentInitiatorDetails;
    }

    /**
     * Sets a new paymentInitiatorDetails
     *
     * @param \Knusperleicht\EpsBankTransfer\Internal\Generated\Payment\V26\PaymentInitiatorDetails $paymentInitiatorDetails
     * @return self
     */
    public function setPaymentInitiatorDetails(?\Knusperleicht\EpsBankTransfer\Internal\Generated\Payment\V26\PaymentInitiatorDetails $paymentInitiatorDetails = null)
    {
        $this->paymentInitiatorDetails = $paymentInitiatorDetails;
        return $this;
    }

    /**
     * Gets as transferMsgDetails
     *
     * @return \Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V26\TransferMsgDetails
     */
    public function getTransferMsgDetails()
    {
        return $this->transferMsgDetails;
    }

    /**
     * Sets a new transferMsgDetails
     *
     * @param \Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V26\TransferMsgDetails $transferMsgDetails
     * @return self
     */
    public function setTransferMsgDetails(?\Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V26\TransferMsgDetails $transferMsgDetails = null)
    {
        $this->transferMsgDetails = $transferMsgDetails;
        return $this;
    }

    /**
     * Adds as webshopArticle
     *
     * @return self
     * @param \Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V26\WebshopArticle $webshopArticle
     */
    public function addToWebshopDetails(\Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V26\WebshopArticle $webshopArticle)
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
     * @return \Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V26\WebshopArticle[]
     */
    public function getWebshopDetails()
    {
        return $this->webshopDetails;
    }

    /**
     * Sets a new webshopDetails
     *
     * @param \Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V26\WebshopArticle[] $webshopDetails
     * @return self
     */
    public function setWebshopDetails(array $webshopDetails = null)
    {
        $this->webshopDetails = $webshopDetails;
        return $this;
    }

    /**
     * Gets as signature
     *
     * @return \Knusperleicht\EpsBankTransfer\Internal\Generated\XmlDsig\Signature
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Sets a new signature
     *
     * @param \Knusperleicht\EpsBankTransfer\Internal\Generated\XmlDsig\Signature $signature
     * @return self
     */
    public function setSignature(?\Knusperleicht\EpsBankTransfer\Internal\Generated\XmlDsig\Signature $signature = null)
    {
        $this->signature = $signature;
        return $this;
    }

    /**
     * Gets as errorDetails
     *
     * @return \Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V26\ErrorDetails
     */
    public function getErrorDetails()
    {
        return $this->errorDetails;
    }

    /**
     * Sets a new errorDetails
     *
     * @param \Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V26\ErrorDetails $errorDetails
     * @return self
     */
    public function setErrorDetails(?\Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V26\ErrorDetails $errorDetails = null)
    {
        $this->errorDetails = $errorDetails;
        return $this;
    }
}


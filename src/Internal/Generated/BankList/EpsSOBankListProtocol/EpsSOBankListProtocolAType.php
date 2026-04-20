<?php

namespace Knusperleicht\EpsBankTransfer\Internal\Generated\BankList\EpsSOBankListProtocol;

/**
 * Class representing EpsSOBankListProtocolAType
 */
class EpsSOBankListProtocolAType
{
    /**
     * @var \Knusperleicht\EpsBankTransfer\Internal\Generated\BankList\BankDataType[] $bank
     */
    private $bank = [
        
    ];

    /**
     * @var \Knusperleicht\EpsBankTransfer\Internal\Generated\BankList\ErrorDataType $errorDetails
     */
    private $errorDetails = null;

    /**
     * Adds as bank
     *
     * @return self
     * @param \Knusperleicht\EpsBankTransfer\Internal\Generated\BankList\BankDataType $bank
     */
    public function addToBank(\Knusperleicht\EpsBankTransfer\Internal\Generated\BankList\BankDataType $bank)
    {
        $this->bank[] = $bank;
        return $this;
    }

    /**
     * isset bank
     *
     * @param int|string $index
     * @return bool
     */
    public function issetBank($index)
    {
        return isset($this->bank[$index]);
    }

    /**
     * unset bank
     *
     * @param int|string $index
     * @return void
     */
    public function unsetBank($index)
    {
        unset($this->bank[$index]);
    }

    /**
     * Gets as bank
     *
     * @return \Knusperleicht\EpsBankTransfer\Internal\Generated\BankList\BankDataType[]
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * Sets a new bank
     *
     * @param \Knusperleicht\EpsBankTransfer\Internal\Generated\BankList\BankDataType[] $bank
     * @return self
     */
    public function setBank(array $bank = null)
    {
        $this->bank = $bank;
        return $this;
    }

    /**
     * Gets as errorDetails
     *
     * @return \Knusperleicht\EpsBankTransfer\Internal\Generated\BankList\ErrorDataType
     */
    public function getErrorDetails()
    {
        return $this->errorDetails;
    }

    /**
     * Sets a new errorDetails
     *
     * @param \Knusperleicht\EpsBankTransfer\Internal\Generated\BankList\ErrorDataType $errorDetails
     * @return self
     */
    public function setErrorDetails(?\Knusperleicht\EpsBankTransfer\Internal\Generated\BankList\ErrorDataType $errorDetails = null)
    {
        $this->errorDetails = $errorDetails;
        return $this;
    }
}


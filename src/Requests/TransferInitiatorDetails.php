<?php
declare(strict_types=1);

/**
 * PSA e-payment standard (EPS) implementation for PHP
 *
 * Copyright 2026 PSA Payment Services Austria GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Knusperleicht\EpsBankTransfer\Requests;

use DateInterval;
use DateTime;
use Exception;
use InvalidArgumentException;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V26\EpsProtocolDetails as EpsProtocolDetailsV26;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V27\EpsProtocolDetails as EpsProtocolDetailsV27;
use Knusperleicht\EpsBankTransfer\Requests\Mappers\TransferInitiatorDetailsV26Mapper;
use Knusperleicht\EpsBankTransfer\Requests\Mappers\TransferInitiatorDetailsV27Mapper;
use Knusperleicht\EpsBankTransfer\Requests\Parts\ObscurityConfig;
use Knusperleicht\EpsBankTransfer\Requests\Parts\PaymentFlowUrls;
use Knusperleicht\EpsBankTransfer\Requests\Parts\WebshopArticle;
use Knusperleicht\EpsBankTransfer\Utilities\Fingerprint;
use Knusperleicht\EpsBankTransfer\Utilities\MoneyFormatter;

/**
 * EPS payment order message
 */
class TransferInitiatorDetails
{

    /**
     * Business partner identification through UserID (= Merchant ID) issued by an eps bank
     * @var string
     */
    private $userId;

    /**
     * Secret given by bank
     * @var string
     */
    private $secret;

    /**
     * Creation date of payment order (xsd::date)
     * @var string
     */
    private $date;

    /**
     * ISO 9362 Bank Identifier Code (BIC) for bank identification
     * @var string
     */
    private $bfiBicIdentifier;

    /**
     * Identification of beneficiary (name and address) in unstructured form. Beneficiary does not need to match account holder.
     * @var string
     */
    private $beneficiaryNameAddressText;

    /**
     * Beneficiary's account details specified by IBAN (International Bank Account Number), e.g. AT611904300234573201 (11-digit account number: 00234573201)
     * @var string
     */
    private $beneficiaryAccountIdentifier;

    /**
     * Payment order message reference, e.g., for merchant research purposes
     * @var string
     */
    private $referenceIdentifier;

    /**
     *
     * @var string|null
     */
    private $unstructuredRemittanceIdentifier;

    /**
     * Unique merchant reference (= beneficiary) for a business transaction that must be returned unchanged to the merchant in payment transactions
     * @var string|null
     */
    private $remittanceIdentifier;

    /**
     * Min/max execution time for eps payment
     * @var string
     */
    private $expirationTime;

    /**
     * For cent values, they must be transmitted separated from the euro amount by a period, e.g. 150.55 (NOT 150,55)!
     * @var string
     */
    private $instructedAmount;

    /**
     * Currency specification according to ISO 4217
     * @var string
     */
    private $amountCurrencyIdentifier = 'EUR';

    /**
     * Array of webshop articles
     * @var WebshopArticle[]
     */
    private $webshopArticles;

    /**
     * Merchant specification of relevant URL addresses
     * @var PaymentFlowUrls
     */
    private $transferMsgDetails;

    /**
     * Optional specification of bank details/BIC of the payment obligor / buyer
     * @var string
     */
    private $orderingCustomerOfiIdentifier;

    /**
     * Optional obscurity configuration (seed and suffix length must be both set when provided)
     * @var ObscurityConfig|null
     */
    private $obscurityConfig = null;

    /**
     * @param string $userId User ID (epsp:UserId)
     * @param string $secret Merchant PIN/secret used in MD5 fingerprint (not transmitted directly)
     * @param string $bfiBicIdentifier BIC of beneficiary bank (epi:BfiBicIdentifier)
     * @param string $beneficiaryNameAddressText Beneficiary name/address (<=140 chars; banks often guarantee 70)
     * @param string $beneficiaryAccountIdentifier Beneficiary IBAN (epi:BeneficiaryAccountIdentifier)
     * @param string $referenceIdentifier Reference Identifier (epi:ReferenceIdentifier)
     * @param int|string $instructedAmount Amount in euro cents (e.g., 9999 = €99.99)
     * @param PaymentFlowUrls $transferMsgDetails Confirmation/redirect URLs (epsp:TransferMsgDetails)
     * @param string|null $date Optional date in YYYY-MM-DD (default: today)
     * @param ObscurityConfig|null $obscurityConfig Optional obscurity configuration for hash generation
     */

    public function __construct(string           $userId,
                                string           $secret,
                                string           $bfiBicIdentifier,
                                string           $beneficiaryNameAddressText,
                                string           $beneficiaryAccountIdentifier,
                                string           $referenceIdentifier,
                                                 $instructedAmount,
                                PaymentFlowUrls  $transferMsgDetails,
                                ?string          $date = null,
                                ?ObscurityConfig $obscurityConfig = null)
    {
        $this->userId = $userId;
        $this->secret = $secret;
        $this->bfiBicIdentifier = $bfiBicIdentifier;
        $this->beneficiaryNameAddressText = $beneficiaryNameAddressText;
        $this->beneficiaryAccountIdentifier = $beneficiaryAccountIdentifier;
        $this->referenceIdentifier = $referenceIdentifier;
        $this->setInstructedAmount($instructedAmount);
        $this->webshopArticles = array();
        $this->transferMsgDetails = $transferMsgDetails;

        $this->date = $date == null ? date('Y-m-d') : $date;
        $this->setObscurityConfig($obscurityConfig);
    }

    /**
     * Sets ExpirationTime by adding a given number of minutes to the current
     * timestamp.
     * @param int $minutes Must be between 5 and 60
     * @throws InvalidArgumentException|Exception if minutes not between 5 and 60
     */
    public function setExpirationMinutes(int $minutes)
    {
        if ($minutes < 5 || $minutes > 60)
            throw new InvalidArgumentException('Expiration minutes value of "' . $minutes . '" is not between 5 and 60.');

        $expires = new DateTime();
        $expires->add(new DateInterval('PT' . $minutes . 'M'));
        $this->expirationTime = $expires->format(DATE_RFC3339);
    }

    /**
     * Set the instructed amount.
     *
     * @param int|string $amount Amount in euro cents (e.g., 9999 = €99.99). Must be integer-like.
     */
    public function setInstructedAmount($amount)
    {
        $this->instructedAmount = MoneyFormatter::formatXsdDecimal($amount);
    }

    private function remittanceForFingerprint(): ?string
    {
        return $this->unstructuredRemittanceIdentifier ?: $this->remittanceIdentifier;
    }

    public function getMD5Fingerprint(): string
    {
        return Fingerprint::generateMD5Fingerprint(
            $this->secret,
            $this->date,
            $this->referenceIdentifier,
            $this->beneficiaryAccountIdentifier,
            (string)$this->remittanceForFingerprint(),
            $this->instructedAmount,
            $this->amountCurrencyIdentifier,
            $this->userId
        );
    }

    public function getSha256Fingerprint(): string
    {
        return Fingerprint::generateInitiateTransferSHA256Fingerprint(
            $this->secret,
            $this->date,
            $this->referenceIdentifier,
            $this->beneficiaryAccountIdentifier,
            (string)$this->remittanceForFingerprint(),
            $this->instructedAmount,
            $this->amountCurrencyIdentifier,
            $this->userId
        );
    }

    /**
     * Domain to EPS schema mapping (v2.6).
     *
     * Creates and populates an EpsProtocolDetails tree from the current
     * TransferInitiatorDetails. All values must adhere to EPS v2.6 rules:
     * - InstructedAmount as decimal string (formatted from euro-cents input)
     * - Exactly one of RemittanceIdentifier or UnstructuredRemittanceIdentifier may be set
     * - MD5Fingerprint derived from merchant secret and core fields
     *
     * @return EpsProtocolDetailsV26 Fully populated protocol details ready to serialize.
     * @throws Exception When date/time parsing or constraints fail.
     */
    public function toV26(): EpsProtocolDetailsV26
    {
        return TransferInitiatorDetailsV26Mapper::map($this);
    }

    /**
     * Domain to EPS schema mapping (v2.7).
     *
     * Mirrors toV26 but uses the V27 generated classes and namespaces.
     *
     * @return EpsProtocolDetailsV27
     * @throws Exception
     */
    public function toV27(): EpsProtocolDetailsV27
    {
        return TransferInitiatorDetailsV27Mapper::map($this);
    }

    public function getWebshopArticles(): array
    {
        return $this->webshopArticles;
    }

    public function setWebshopArticles(array $webshopArticles): void
    {
        $this->webshopArticles = $webshopArticles;
    }

    public function addArticle(WebshopArticle $article): void
    {
        $this->webshopArticles[] = $article;
    }

    /**
     * @param string $remittanceIdentifier
     */
    public function setRemittanceIdentifier(string $remittanceIdentifier): void
    {
        $this->remittanceIdentifier = $remittanceIdentifier;
    }

    /**
     * @param string $unstructuredRemittanceIdentifier
     */
    public function setUnstructuredRemittanceIdentifier(string $unstructuredRemittanceIdentifier): void
    {
        $this->unstructuredRemittanceIdentifier = $unstructuredRemittanceIdentifier;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

    /**
     * @return false|string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param false|string $date
     */
    public function setDate($date): void
    {
        $this->date = $date;
    }

    public function getBfiBicIdentifier(): string
    {
        return $this->bfiBicIdentifier;
    }

    public function setBfiBicIdentifier(string $bfiBicIdentifier): void
    {
        $this->bfiBicIdentifier = $bfiBicIdentifier;
    }

    public function getBeneficiaryNameAddressText(): string
    {
        return $this->beneficiaryNameAddressText;
    }

    public function setBeneficiaryNameAddressText(string $beneficiaryNameAddressText): void
    {
        $this->beneficiaryNameAddressText = $beneficiaryNameAddressText;
    }

    public function getBeneficiaryAccountIdentifier(): string
    {
        return $this->beneficiaryAccountIdentifier;
    }

    public function setBeneficiaryAccountIdentifier(string $beneficiaryAccountIdentifier): void
    {
        $this->beneficiaryAccountIdentifier = $beneficiaryAccountIdentifier;
    }

    public function getReferenceIdentifier(): string
    {
        return $this->referenceIdentifier;
    }

    public function setReferenceIdentifier(string $referenceIdentifier): void
    {
        $this->referenceIdentifier = $referenceIdentifier;
    }

    public function getExpirationTime(): ?string
    {
        return $this->expirationTime;
    }

    public function setExpirationTime(string $expirationTime): void
    {
        $this->expirationTime = $expirationTime;
    }

    public function getAmountCurrencyIdentifier(): string
    {
        return $this->amountCurrencyIdentifier;
    }

    public function setAmountCurrencyIdentifier(string $amountCurrencyIdentifier): void
    {
        $this->amountCurrencyIdentifier = $amountCurrencyIdentifier;
    }

    public function getTransferMsgDetails(): PaymentFlowUrls
    {
        return $this->transferMsgDetails;
    }

    public function setTransferMsgDetails(PaymentFlowUrls $transferMsgDetails): void
    {
        $this->transferMsgDetails = $transferMsgDetails;
    }

    public function getOrderingCustomerOfiIdentifier(): ?string
    {
        return $this->orderingCustomerOfiIdentifier;
    }

    public function setOrderingCustomerOfiIdentifier(string $orderingCustomerOfiIdentifier): void
    {
        $this->orderingCustomerOfiIdentifier = $orderingCustomerOfiIdentifier;
    }

    public function getObscurityConfig(): ?ObscurityConfig
    {
        return $this->obscurityConfig;
    }

    public function setObscurityConfig($config): void
    {
        $this->obscurityConfig = $config;
    }

    /**
     * @return string
     */
    public function getRemittanceIdentifier(): ?string
    {
        return $this->remittanceIdentifier;
    }

    /**
     * @return string
     */
    public function getInstructedAmount(): string
    {
        return $this->instructedAmount;
    }

    /**
     * @return string
     */
    public function getUnstructuredRemittanceIdentifier(): ?string
    {
        return $this->unstructuredRemittanceIdentifier;
    }
}
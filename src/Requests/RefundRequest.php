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

use DateTime;
use Exception;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Refund\Amount;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Refund\AuthenticationDetails;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Refund\EpsRefundRequest;
use Knusperleicht\EpsBankTransfer\Utilities\Fingerprint;
use Knusperleicht\EpsBankTransfer\Utilities\MoneyFormatter;

class RefundRequest
{
    /**
     * @var string Creation timestamp in ISO 8601 format
     */
    public $creDtTm;

    /**
     * @var string Transaction identifier (1-36 chars: [a-zA-Z0-9-._~])
     */
    public $transactionId;

    /**
     * @var string Merchant IBAN (up to 34 chars)
     */
    public $merchantIban;

    /**
     * @var string Refund amount as decimal (e.g., "1.00").
     */
    public $amount;

    /**
     * @var string Three-letter currency code
     */
    public $amountCurrencyIdentifier;

    /**
     * @var string|null Reference ID for refund (max 35 chars)
     */
    public $refundReference;

    /**
     * @var string User ID (max 25 chars)
     */
    public $userId;

    /**
     * @var string Authentication PIN
     */
    public $pin;

    public function __construct(
        string  $creDtTm,
        string  $transactionId,
        string  $merchantIban,
                $amount,
        string  $amountCurrencyIdentifier,
        string  $userId,
        string  $pin,
        ?string $refundReference = null
    )
    {
        $this->creDtTm = $creDtTm;
        $this->transactionId = $transactionId;
        $this->merchantIban = $merchantIban;
        $this->setAmount($amount);
        $this->amountCurrencyIdentifier = $amountCurrencyIdentifier;
        $this->refundReference = $refundReference;
        $this->userId = $userId;
        $this->pin = $pin;
    }

    /**
     * Set the amount.
     *
     * @param int|string $amount Amount in euro cents (e.g., 9999 = €99.99). Must be integer-like.
     */
    public function setAmount($amount): void
    {
        $this->amount = MoneyFormatter::formatXsdDecimal($amount);
    }

    /**
     * Domain to EPS schema mapping (Refund v2.6).
     *
     * Populates EpsRefundRequest from the domain RefundRequest. Amount must be a decimal string
     * (e.g., "1.00"). Currency must be ISO 4217 (EPS refund supports EUR).
     *
     * @return EpsRefundRequest Fully populated refund request.
     * @throws Exception When date parsing fails or invalid values are given.
     */
    public function toV26(): EpsRefundRequest
    {
        $refundRequest = new EpsRefundRequest();

        $refundRequest->setCreDtTm(new DateTime($this->creDtTm));
        $refundRequest->setTransactionId($this->transactionId);
        $refundRequest->setMerchantIBAN($this->merchantIban);

        $amount = new Amount($this->amount);
        $amount->setAmountCurrencyIdentifier($this->amountCurrencyIdentifier);
        $refundRequest->setAmount($amount);

        if (!empty($this->refundReference)) {
            $refundRequest->setRefundReference($this->refundReference);
        }

        $auth = new AuthenticationDetails();
        $auth->setUserId($this->userId);

        $fingerprint = Fingerprint::generateRefundSHA256Fingerprint(
            $this->pin,
            $this->creDtTm,
            $this->transactionId,
            $this->merchantIban,
            $this->amount,
            $this->amountCurrencyIdentifier,
            $this->userId,
            $this->refundReference
        );
        $auth->setSHA256Fingerprint($fingerprint);

        $refundRequest->setAuthenticationDetails($auth);

        return $refundRequest;
    }
}
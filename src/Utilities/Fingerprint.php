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

namespace Knusperleicht\EpsBankTransfer\Utilities;

/**
 * Utility helpers to generate fingerprints required by the EPS protocol.
 *
 * Provides MD5 (v2.6) and SHA-256 (v2.7) fingerprint generation methods.
 */
class Fingerprint
{

    /**
     * Generate MD5 fingerprint for EPS v2.6 requests.
     *
     * @param string $secret Merchant secret provided by bank.
     * @param string $date Creation date (YYYY-MM-DD).
     * @param string $reference Reference identifier.
     * @param string $account Beneficiary IBAN.
     * @param string $remittance Remittance identifier (structured or unstructured).
     * @param string $amount Amount value with dot as decimal separator, e.g. 150.55.
     * @param string $currency ISO 4217 currency code (e.g., EUR).
     * @param string $userId Merchant UserID (Merchant ID).
     * @return string Uppercase hexadecimal MD5 hash string.
     */
    public static function generateMD5Fingerprint(
        string $secret, string $date, string $reference,
        string $account, string $remittance, string $amount,
        string $currency, string $userId): string
    {
        return md5($secret . $date . $reference . $account . $remittance . $amount . $currency . $userId);
    }

    /**
     * Generate SHA-256 fingerprint for EPS v2.6 refund requests.
     *
     * @param string $pin Shared secret (PIN) provided by bank.
     * @param string $creationDateTime ISO-8601 datetime (UTC) of the request.
     * @param string $transactionId EPS transaction identifier.
     * @param string $merchantIban Merchant IBAN.
     * @param string|int|float $amountValue Amount value as string or numeric; will be concatenated as-is.
     * @param string $amountCurrency ISO 4217 currency code (e.g., EUR).
     * @param string $userId Merchant UserID.
     * @param string|null $refundReference Optional refund reference.
     * @return string Uppercase hexadecimal SHA-256 hash string.
     */
    public static function generateRefundSHA256Fingerprint(string $pin, string $creationDateTime,
                                                           string $transactionId, string $merchantIban,
                                                                  $amountValue, string $amountCurrency,
                                                           string $userId, ?string $refundReference = null): string
    {
        $inputData = $pin .
            $creationDateTime .
            $transactionId .
            $merchantIban .
            $amountValue .
            $amountCurrency .
            $refundReference .
            $userId;

        return strtoupper(hash('sha256', $inputData));
    }

    /**
     * Generate SHA-256 fingerprint for EPS v2.7 initiate transfer requests.
     *
     * @param string $pin Shared secret (PIN) provided by bank.
     * @param string $creationDateTime ISO-8601 datetime (UTC) of the request.
     * @param string $referenceIdentifier Reference identifier.
     * @param string $beneficiaryAccountIdentifier Beneficiary IBAN.
     * @param string $remittanceIdentifier Structured or unstructured remittance identifier.
     * @param string|int|float $amountValue Amount value as string or numeric; will be concatenated as-is.
     * @param string $amountCurrency ISO 4217 currency code (e.g., EUR).
     * @param string $userId Merchant UserID.
     * @return string Uppercase hexadecimal SHA-256 hash string.
     */
    public static function generateInitiateTransferSHA256Fingerprint(string $pin, string $creationDateTime,
                                                                     string $referenceIdentifier, string $beneficiaryAccountIdentifier,
                                                                     string $remittanceIdentifier,
                                                                            $amountValue, string $amountCurrency,
                                                                     string $userId): string
    {
        $inputData = $pin .
            $creationDateTime .
            $referenceIdentifier .
            $beneficiaryAccountIdentifier .
            $remittanceIdentifier .
            $amountValue .
            $amountCurrency .
            $userId;

        return strtoupper(hash('sha256', $inputData));
    }
}

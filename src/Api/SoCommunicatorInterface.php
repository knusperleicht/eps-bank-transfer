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

namespace Knusperleicht\EpsBankTransfer\Api;

use InvalidArgumentException;
use Knusperleicht\EpsBankTransfer\Domain\BankList;
use Knusperleicht\EpsBankTransfer\Domain\ProtocolDetails;
use Knusperleicht\EpsBankTransfer\Domain\RefundResponse;
use Knusperleicht\EpsBankTransfer\Exceptions\BankListException;
use Knusperleicht\EpsBankTransfer\Exceptions\CallbackResponseException;
use Knusperleicht\EpsBankTransfer\Exceptions\InvalidCallbackException;
use Knusperleicht\EpsBankTransfer\Exceptions\XmlValidationException;
use Knusperleicht\EpsBankTransfer\Requests\RefundRequest;
use Knusperleicht\EpsBankTransfer\Requests\TransferInitiatorDetails;
use LogicException;

/**
 * Public API interface for interacting with the EPS Scheme Operator (SO).
 *
 * This interface abstracts high-level operations:
 * - Fetching the bank list supported by EPS
 * - Initiating a payment (transfer initiator)
 * - Requesting a refund
 * - Handling callback requests (confirmation and vitality check)
 */
interface SoCommunicatorInterface
{
    /**
     * Fetches the current bank list from the Scheme Operator (SO).
     *
     * The bank list is currently available for interface version 2.6 only.
     *
     * @param string $version Interface version ("2.6" or "2.7"). Bank list is 2.6.
     * @param string|null $targetUrl Optional custom target URL instead of the default.
     * @return BankList List of supported banks.
     * @throws InvalidArgumentException When an unsupported version is provided.
     * @throws BankListException When SO responds with an error payload.
     * @throws XmlValidationException When response XML fails XSD validation.
     * @throws LogicException For version 2.7 (not implemented yet).
     */
    public function getBanks(string $version = '2.6', ?string $targetUrl = null): BankList;

    /**
     * Sends a Transfer Initiator request to the Scheme Operator (SO).
     *
     * @param TransferInitiatorDetails $transferInitiatorDetails Details of the payment initiation.
     * @param string $version Version of the SO interface ("2.6" or "2.7").
     * @param string|null $targetUrl Optional custom target URL instead of the default.
     * @return ProtocolDetails Result of the request mapped into a domain object.
     * @throws InvalidArgumentException When an unsupported version is provided.
     * @throws XmlValidationException When request/response XML validation fails.
     * @throws LogicException For version 2.7 (not implemented yet).
     */
    public function sendTransferInitiatorDetails(
        TransferInitiatorDetails $transferInitiatorDetails,
        string                   $version = '2.6',
        ?string                  $targetUrl = null
    ): ProtocolDetails;

    /**
     * Sends a refund request to the Scheme Operator (SO).
     *
     * Refund is currently available for interface version 2.6 only.
     *
     * @param RefundRequest $refundRequest Refund request details.
     * @param string $version Version of the SO interface ("2.6" or "2.7").
     * @param string|null $targetUrl Optional custom target URL instead of the default.
     * @return RefundResponse Result of the request mapped into a domain object.
     * @throws InvalidArgumentException When an unsupported version is provided.
     * @throws XmlValidationException When request/response XML validation fails.
     * @throws LogicException For version 2.7 (not implemented yet).
     */
    public function sendRefundRequest(
        RefundRequest $refundRequest,
        string        $version = '2.6',
        ?string       $targetUrl = null
    ): RefundResponse;

    /**
     * Processes callback requests from the SO (Bank Confirmation / VitalityCheck).
     *
     * The confirmation callback should return true when the confirmation has been processed successfully.
     * The vitality check callback (optional) should return true for a valid vitality check.
     *
     * @param callable|null $confirmationCallback Callback invoked for payment confirmations. Must return true.
     * @param callable|null $vitalityCheckCallback Optional callback for vitality checks. Must return true.
     * @param string $rawPostStream Input stream to read the raw POST data (e.g. "php://input").
     * @param string $outputStream Output stream to write the response (e.g. "php://output").
     * @param string $version Interface version ("2.6" or "2.7").
     * @throws InvalidCallbackException When callbacks are missing or not callable.
     * @throws XmlValidationException When request/response XML validation fails.
     * @throws CallbackResponseException When a callback does not return true.
     * @throws LogicException For version 2.7 (not implemented yet).
     */
    public function handleConfirmationUrl(
        $confirmationCallback = null,
        $vitalityCheckCallback = null,
        string $rawPostStream = 'php://input',
        string $outputStream = 'php://output',
        string $version = '2.6'
    ): void;

    /**
     * Configuration: Set the base URL of the Scheme Operator (e.g., LIVE or TEST).
     *
     * @param string $baseUrl Base URL used for outgoing requests.
     */
    public function setBaseUrl(string $baseUrl): void;
}

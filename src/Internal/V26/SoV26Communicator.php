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

namespace Knusperleicht\EpsBankTransfer\Internal\V26;

use Exception;
use Knusperleicht\EpsBankTransfer\Domain\BankConfirmationDetails;
use Knusperleicht\EpsBankTransfer\Domain\VitalityCheckDetails;
use Knusperleicht\EpsBankTransfer\Exceptions\XmlValidationException;
use Knusperleicht\EpsBankTransfer\Internal\AbstractSoCommunicator;
use Knusperleicht\EpsBankTransfer\Internal\Generated\BankList\EpsSOBankListProtocol;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V26\EpsProtocolDetails;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Refund\EpsRefundResponse;
use Knusperleicht\EpsBankTransfer\Requests\RefundRequest;
use Knusperleicht\EpsBankTransfer\Responses\ShopResponseDetails;
use Knusperleicht\EpsBankTransfer\Utilities\XmlValidator;

/**
 * Internal communicator for EPS interface version 2.6.
 *
 * Encapsulates the low-level HTTP calls, XML serialization and validation
 * required by the EPS Scheme Operator for v2.6 endpoints (bank list, transfer
 * initiator, refund, confirmations).
 */
class SoV26Communicator extends AbstractSoCommunicator
{
    public const BANKLIST = '/data/haendler/v2_6';
    public const REFUND = '/refund/eps/v2_6';
    public const TRANSFER = '/transinit/eps/v2_6';
    public const VERSION = '2.6';

    protected function getVersion(): string
    {
        return self::VERSION;
    }

    protected function getTransferPath(): string
    {
        return self::TRANSFER;
    }

    protected function protocolClassFqn(): string
    {
        return EpsProtocolDetails::class;
    }

    protected function serializeTransferInitiator($transferInitiatorDetails): string
    {
        return $this->serializer->serialize($transferInitiatorDetails->toV26(), 'xml');
    }

    protected function vitalityFromProtocol($protocol): ?VitalityCheckDetails
    {
        return $protocol->getVitalityCheckDetails() ? VitalityCheckDetails::fromV26($protocol->getVitalityCheckDetails()) : null;
    }

    protected function bankConfirmationFromProtocol($protocol): ?BankConfirmationDetails
    {
        return $protocol->getBankConfirmationDetails() ? BankConfirmationDetails::fromV26($protocol) : null;
    }

    protected function shopResponseXml(ShopResponseDetails $details): string
    {
        return $this->serializer->serialize($details->toV26(), 'xml');
    }

    protected function getBankListPath(): string
    {
        return self::BANKLIST;
    }

    /**
     * Send a refund request to EPS v2.6 endpoint.
     *
     * @param RefundRequest $refundRequest Domain refund request
     * @param string|null $targetUrl Optional override of the endpoint URL
     * @return EpsRefundResponse Parsed EPS refund response
     * @throws XmlValidationException When response XML is invalid
     * @throws Exception On underlying HTTP/serialization errors
     */
    public function sendRefundRequest(
        RefundRequest $refundRequest,
        ?string       $targetUrl = null
    ): EpsRefundResponse
    {
        $targetUrl = $targetUrl ?? $this->core->getBaseUrl() . self::REFUND;

        $xmlData = $this->serializer->serialize($refundRequest->toV26(), 'xml');
        $responseXml = $this->core->postUrl(
            $targetUrl,
            $xmlData,
            'Sending refund request to ' . $targetUrl
        );

        XmlValidator::validateEpsRefund($responseXml, self::VERSION);

        return $this->serializer->deserialize($responseXml, EpsRefundResponse::class, 'xml');
    }

}

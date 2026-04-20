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

namespace Knusperleicht\EpsBankTransfer\Internal\V27;

use Knusperleicht\EpsBankTransfer\Domain\BankConfirmationDetails;
use Knusperleicht\EpsBankTransfer\Domain\VitalityCheckDetails;
use Knusperleicht\EpsBankTransfer\Exceptions\XmlValidationException;
use Knusperleicht\EpsBankTransfer\Internal\AbstractSoCommunicator;
use Knusperleicht\EpsBankTransfer\Internal\Generated\BankList\EpsSOBankListProtocol;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V27\EpsProtocolDetails;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Refund\EpsRefundResponse;
use Knusperleicht\EpsBankTransfer\Requests\RefundRequest;
use Knusperleicht\EpsBankTransfer\Responses\ShopResponseDetails;
use Knusperleicht\EpsBankTransfer\Utilities\XmlValidator;
use LogicException;

/**
 * Internal communicator for EPS interface version 2.7.
 *
 * Note: Functionality is intentionally not implemented yet because the official
 * XSD 2.7 is pending. All public methods throw LogicException to make the
 * limitation explicit to integrators while keeping the public API forward-compatible.
 */
class SoV27Communicator extends AbstractSoCommunicator
{
    public const BANKLIST = '/data/haendler/v2_7';
    public const TRANSFER = '/transinit/eps/v2_7';
    public const VERSION = '2.7';

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
        return $this->serializer->serialize($transferInitiatorDetails->toV27(), 'xml');
    }

    protected function vitalityFromProtocol($protocol): ?VitalityCheckDetails
    {
        return $protocol->getVitalityCheckDetails() ? VitalityCheckDetails::fromV27($protocol->getVitalityCheckDetails()) : null;
    }

    protected function bankConfirmationFromProtocol($protocol): ?BankConfirmationDetails
    {
        return $protocol->getBankConfirmationDetails() ? BankConfirmationDetails::fromV27($protocol) : null;
    }

    protected function shopResponseXml(ShopResponseDetails $details): string
    {
        return $this->serializer->serialize($details->toV27(), 'xml');
    }

    protected function getBankListPath(): string
    {
        return self::BANKLIST;
    }

    /**
     * Send a refund request using the v2.7 interface.
     *
     * Not implemented until XSD 2.7 is available.
     *
     * @param RefundRequest $refundRequest Refund request details.
     * @param string|null $targetUrl Optional custom target URL instead of the default.
     * @return EpsRefundResponse
     * @throws LogicException Always thrown until v2.7 support is implemented.
     */
    public function sendRefundRequest(
        RefundRequest $refundRequest,
        ?string       $targetUrl = null
    ): EpsRefundResponse
    {
        throw new LogicException('Not implemented yet - use version 2.6');
    }
}

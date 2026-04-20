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

namespace Knusperleicht\EpsBankTransfer\Domain;

use Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V26\EpsProtocolDetails as V26Details;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V27\EpsProtocolDetails as V27Details;

/**
 * Result of an EPS transfer initiation mapped into a compact domain object.
 *
 * Contains error code/message and the optional client redirect URL.
 */
class ProtocolDetails
{
    /** @var string|null */
    private $errorCode;

    /** @var string|null */
    private $errorMessage;

    /** @var string|null */
    private $clientRedirectUrl;

    /** @var string|null */
    private $transactionId;

    /**
     * Create protocol details.
     *
     * @param string|null $errorCode EPS error code (e.g., "000" for no error) or null.
     * @param string|null $errorMessage Error message or null.
     * @param string|null $clientRedirectUrl Redirect URL for the client if provided by the SO.
     * @param string|null $transactionId Transaction ID if provided by the SO.
     */
    public function __construct(?string $errorCode, ?string $errorMessage, ?string $clientRedirectUrl = null, ?string $transactionId = null)
    {
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
        $this->clientRedirectUrl = $clientRedirectUrl;
        $this->transactionId = $transactionId;
    }

    /**
     * Map v2.6 generated protocol details to domain model.
     */
    public static function fromV26(V26Details $details): ProtocolDetails
    {
        $bankResponse = $details->getBankResponseDetails();
        $error = $bankResponse->getErrorDetails();

        $transactionId = $bankResponse->getTransactionId();

        return new self(
            $error ? $error->getErrorCode() : null,
            $error ? $error->getErrorMsg() : null,
            $bankResponse->getClientRedirectUrl(),
            $transactionId
        );
    }

    /**
     * Map v2.7 generated protocol details to domain model.
     */
    public static function fromV27(V27Details $details): ProtocolDetails
    {
        $bankResponse = $details->getBankResponseDetails();
        $error = $bankResponse->getErrorDetails();

        return new self(
            $error ? $error->getErrorCode() : null,
            $error ? $error->getErrorMsg() : null,
            $bankResponse->getClientRedirectUrl(),
            $bankResponse->getTransactionId()
        );
    }

    /**
     * EPS error code ("000" means no error), if available.
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * Human-readable error message, if provided.
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * URL to redirect the client to continue payment, if present.
     */
    public function getClientRedirectUrl(): ?string
    {
        return $this->clientRedirectUrl;
    }

    /**
     * Transaction ID if provided by the SO.
     */
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }
}
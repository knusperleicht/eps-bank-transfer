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

use Knusperleicht\EpsBankTransfer\Internal\Generated\Refund\EpsRefundResponse;

/**
 * Result of an EPS refund request mapped into a domain object.
 */
class RefundResponse
{
    private $statusCode;
    private $errorMessage;

    /**
     * Create a refund response.
     *
     * @param string $statusCode Status code ("000" means success at SO level).
     * @param string|null $errorMessage Optional error message.
     */
    public function __construct(string $statusCode, ?string $errorMessage)
    {
        $this->statusCode = $statusCode;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Map v2.6 generated refund response to domain model.
     */
    public static function fromV26(EpsRefundResponse $response): self
    {
        return new self(
            $response->getStatusCode(),
            $response->getErrorMsg() ?? null
        );
    }

    /** Status code of the refund request as returned by the SO. */
    public function getStatusCode(): string
    {
        return $this->statusCode;
    }

    /** Optional error message returned by the SO. */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }
}

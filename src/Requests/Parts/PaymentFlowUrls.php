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

namespace Knusperleicht\EpsBankTransfer\Requests\Parts;

/**
 * Value object holding callback/redirect URLs for the EPS payment flow.
 */
class PaymentFlowUrls
{

    /** @var string The URL where the eps SO sends the vitality check and eps payment confirmation message */
    private $confirmationUrl;

    /** @var string The URL to guarantee a continuous flow for the buyer and offer a return point to the merchant's webshop */
    private $transactionOkUrl;

    /** @var string If the transaction was not completed successfully, the buyer will be redirected to this URL after system feedback */
    private $transactionNokUrl;

    /**
     *
     * @param string $confirmationUrl
     * @param string $transactionOkUrl
     * @param string $transactionNokUrl
     */
    public function __construct(string $confirmationUrl, string $transactionOkUrl, string $transactionNokUrl)
    {
        $this->confirmationUrl = $confirmationUrl;
        $this->transactionOkUrl = $transactionOkUrl;
        $this->transactionNokUrl = $transactionNokUrl;
    }

    public function getConfirmationUrl(): string
    {
        return $this->confirmationUrl;
    }

    public function setConfirmationUrl(string $confirmationUrl): void
    {
        $this->confirmationUrl = $confirmationUrl;
    }

    public function getTransactionOkUrl(): string
    {
        return $this->transactionOkUrl;
    }

    public function setTransactionOkUrl(string $transactionOkUrl): void
    {
        $this->transactionOkUrl = $transactionOkUrl;
    }

    public function getTransactionNokUrl(): string
    {
        return $this->transactionNokUrl;
    }
}
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

/**
 * Value object representing a bank entry from the EPS bank list.
 *
 * Contains BIC, display name, optional EPS URL and country code.
 */
class Bank
{
    /** @var string */
    private $bic;

    /** @var string */
    private $name;

    /** @var string|null */
    private $url;

    /** @var string */
    private $countryCode;

    /** @var array<string> */
    private $nationalPaymentTypes;

    /** @var string|null */
    private $internationalPaymentType;

    /** @var bool|null */
    private $app2app;

    /**
     * Create a bank value object.
     *
     * @param string $bic BIC identifier of the bank.
     * @param string $name Display name of the bank.
     * @param string|null $url Optional EPS URL for the bank.
     * @param string $countryCode ISO country code for the bank.
     * @param array<string> $nationalPaymentTypes Array of national payment types (EPG, EPN, EPF).
     * @param string|null $internationalPaymentType International payment type (EPG only).
     * @param bool|null $app2app Whether app2app is supported.
     */
    public function __construct(
        string  $bic,
        string  $name,
        ?string $url = null,
        string  $countryCode = '',
        array   $nationalPaymentTypes = [],
        ?string $internationalPaymentType = null,
        ?bool   $app2app = null
    )
    {
        $this->bic = $bic;
        $this->name = $name;
        $this->url = $url;
        $this->countryCode = $countryCode;
        $this->nationalPaymentTypes = $nationalPaymentTypes;
        $this->internationalPaymentType = $internationalPaymentType;
        $this->app2app = $app2app;
    }

    /**
     * @return string
     */
    public function getBic(): string
    {
        return $this->bic;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * @return array<string>
     */
    public function getNationalPaymentTypes(): array
    {
        return $this->nationalPaymentTypes;
    }

    /**
     * @return string|null
     */
    public function getInternationalPaymentType(): ?string
    {
        return $this->internationalPaymentType;
    }

    /**
     * @return bool|null
     */
    public function isApp2app(): ?bool
    {
        return $this->app2app;
    }
}
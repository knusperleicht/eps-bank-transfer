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

use Knusperleicht\EpsBankTransfer\Internal\Generated\BankList\EpsSOBankListProtocol;

/**
 * Collection of Bank value objects.
 *
 * Provides mapping helpers from generated XSD types into domain entities.
 */
class BankList
{
    /** @var Bank[] */
    private $banks;

    /**
     * Create a bank list.
     *
     * @param Bank[] $banks Array of banks.
     */
    public function __construct(array $banks)
    {
        $this->banks = $banks;
    }

    /**
     * Map generated v2.6 bank list protocol to domain model.
     *
     * @param EpsSOBankListProtocol $protocol Parsed XSD object.
     * @return self
     */
    public static function from(EpsSOBankListProtocol $protocol): self
    {
        $banks = [];
        foreach ($protocol->getBank() as $bank) {
            $banks[] = new Bank(
                $bank->getBic(),
                $bank->getBezeichnung(),
                $bank->getEpsUrl(),
                $bank->getLand(),
                $bank->getZahlungsweiseNat(),
                $bank->getZahlungsweiseInt(),
                $bank->getApp2app()
            );
        }
        return new self($banks);
    }

    /**
     * Get all banks.
     *
     * @return Bank[]
     */
    public function getBanks(): array
    {
        return $this->banks;
    }
}

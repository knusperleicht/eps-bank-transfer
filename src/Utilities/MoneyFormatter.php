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

use InvalidArgumentException;

final class MoneyFormatter
{
    /**
     * Convert an integer euro-cent amount to EPS XSD decimal string (e.g., 1234 -> "12.34").
     *
     * @param int|string $val Integer cents (or numeric string of digits)
     * @return string Decimal amount with two fraction digits and dot as separator
     * @throws InvalidArgumentException When the value is not an int or digit string
     */
    public static function formatXsdDecimal($val): string
    {
        if (is_string($val) && ctype_digit($val)) {
            $val = (int)$val;
        }

        if (!is_int($val)) {
            throw new InvalidArgumentException(
                sprintf("Int value (cents) expected but %s received", gettype($val))
            );
        }

        $formatted = number_format($val / 100, 2, '.', '');
        return sprintf("%.2f", (float)$formatted);
    }
}
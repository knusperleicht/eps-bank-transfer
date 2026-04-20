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

use Knusperleicht\EpsBankTransfer\Utilities\MoneyFormatter;

class WebshopArticle
{

    /** @var string name */
    public $name;

    /** @var number of items */
    public $count;

    /** @var string representation of price */
    public $price;

    /**
     *
     * @param string $name item name
     * @param int $count number of items
     * @param int $price price in cents
     */
    public function __construct(string $name, int $count, int $price)
    {
        $this->name = $name;
        $this->count = $count;
        $this->setPrice($price);
    }

    /**
     *
     * @param int $value in cents
     */
    public function setPrice(int $value)
    {
        $this->price = MoneyFormatter::formatXsdDecimal($value);
    }
}


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

use InvalidArgumentException;

/**
 * Value object representing obscurity (hash suffix) configuration.
 *
 * Both properties must be provided together: length (>=0) and seed (string when length>0).
 */
class ObscurityConfig
{
    /** @var int */
    private $length;
    /** @var string|null */
    private $seed;

    public function __construct(int $length, ?string $seed)
    {
        if ($length < 0) {
            throw new InvalidArgumentException('Obscurity length must be a non-negative integer.');
        }
        $this->length = $length;
        $this->seed = $seed;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getSeed(): ?string
    {
        return $this->seed;
    }
}

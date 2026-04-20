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

namespace Knusperleicht\EpsBankTransfer\Serializer;

use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Visitor\SerializationVisitorInterface;

class NoCdataXmlSerializationVisitor implements SerializationVisitorInterface
{
    /** @var SerializationVisitorInterface */
    private $delegateVisitor;

    public function __construct(SerializationVisitorInterface $delegateVisitor)
    {
        $this->delegateVisitor = $delegateVisitor;
    }

    public function visitString(string $stringValue, array $type)
    {
        return $this->delegateVisitor->getDocument()->createTextNode((string)$stringValue);
    }

    public function visitNull($data, array $type)
    {
        return $this->delegateVisitor->visitNull($data, $type);
    }

    public function visitBoolean(bool $data, array $type)
    {
        return $this->delegateVisitor->visitBoolean($data, $type);
    }

    public function visitInteger(int $data, array $type)
    {
        return $this->delegateVisitor->visitInteger($data, $type);
    }

    public function visitDouble(float $data, array $type)
    {
        return $this->delegateVisitor->visitDouble($data, $type);
    }

    public function visitArray(array $data, array $type): void
    {
        $this->delegateVisitor->visitArray($data, $type);
    }

    public function startVisitingObject(ClassMetadata $metadata, object $data, array $type): void
    {
        $this->delegateVisitor->startVisitingObject($metadata, $data, $type);
    }

    public function visitProperty(PropertyMetadata $metadata, $value): void
    {
        $this->delegateVisitor->visitProperty($metadata, $value);
    }

    public function endVisitingObject(ClassMetadata $metadata, object $data, array $type): void
    {
        $this->delegateVisitor->endVisitingObject($metadata, $data, $type);
    }

    public function getResult($data)
    {
        return $this->delegateVisitor->getResult($data);
    }

    public function prepare($data)
    {
        return $this->delegateVisitor->prepare($data);
    }

    public function getDocument()
    {
        return $this->delegateVisitor->getDocument();
    }

    public function setNavigator(GraphNavigatorInterface $navigator): void
    {
        $this->delegateVisitor->setNavigator($navigator);
    }
}
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

use DOMDocument;
use Exception;
use Knusperleicht\EpsBankTransfer\Exceptions\XmlValidationException;

/**
 * XML validation utilities for EPS schemas.
 *
 * Provides helpers to validate BankList, EPS Protocol, and EPS Refund XML
 * against the bundled XSD schemas for supported interface versions.
 */
class XmlValidator
{
    private const VERSION_MAPPING = [
        '2.6' => 'V26',
        '2.7' => 'V27'
    ];

    /**
     * Validate a BankList XML string against the BankList XSD.
     *
     * @param string $xml The XML content to validate.
     * @return bool True if validation succeeds.
     * @throws XmlValidationException When the XML is empty, malformed, or invalid.
     */
    public static function validateBankList(string $xml): bool
    {
        return self::validateXml($xml, self::gtXSD('epsSOBankListProtocol.xsd'));
    }

    /**
     * Validate an EPS Protocol XML content against the version-specific XSD.
     *
     * @param string $xml The XML content to validate.
     * @param string $version Interface version (e.g., "2.6" or "2.7").
     * @return bool True if validation succeeds.
     * @throws XmlValidationException When XML is empty, malformed, or invalid.
     */
    public static function validateEpsProtocol(string $xml, string $version = '2.6'): bool
    {
        $mappedVersion = self::VERSION_MAPPING[$version] ?? 'V26';
        $filename = "EPSProtocol-{$mappedVersion}.xsd";
        return self::validateXml($xml, self::gtXSD($filename));
    }

    /**
     * Validate an EPS Refund XML content against the version-specific XSD.
     *
     * @param string $xml The XML content to validate.
     * @param string $version Interface version (e.g., "2.6" or "2.7").
     * @return bool True if validation succeeds.
     * @throws XmlValidationException When XML is empty, malformed, or invalid.
     */
    public static function validateEpsRefund(string $xml, string $version = '2.6'): bool
    {
        $mappedVersion = self::VERSION_MAPPING[$version] ?? 'V26';
        $filename = "EPSRefund-{$mappedVersion}.xsd";
        return self::validateXml($xml, self::gtXSD($filename));
    }

    /**
     * Resolve the absolute path to an XSD schema file in resources/schemas.
     *
     * @param string $filename Schema filename.
     * @return string Absolute path to XSD file.
     */
    private static function gtXSD(string $filename): string
    {
        return dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR . 'resources'
            . DIRECTORY_SEPARATOR . 'schemas'
            . DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * Validate XML string against an XSD schema file.
     *
     * @param string $xml XML content.
     * @param string $xsd Absolute path to the XSD file.
     * @return bool True if validation succeeds.
     * @throws XmlValidationException When parsing or schema validation fails.
     */
    private static function validateXml(string $xml, string $xsd): bool
    {
        if (empty($xml)) {
            throw new XmlValidationException('XML is empty');
        }
        $doc = new DOMDocument();
        try {
            $doc->loadXML($xml);
        } catch (Exception $e) {
            throw new XmlValidationException('Failed to load XML: ' . $e->getMessage());
        }
        $prevState = libxml_use_internal_errors(true);
        if (!$doc->schemaValidate($xsd)) {
            $xmlError = libxml_get_last_error();
            libxml_use_internal_errors($prevState);

            throw new XmlValidationException('XML does not validate against XSD.');
        }
        libxml_use_internal_errors($prevState);
        return true;
    }
}
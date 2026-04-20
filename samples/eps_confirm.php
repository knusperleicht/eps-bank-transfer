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
require_once __DIR__ . '/../vendor/autoload.php';

// Optional: interface version can be set in samples/config.local.php (key: 'interface_version').
// If not provided, the library default will be used (currently '2.6').

use Knusperleicht\EpsBankTransfer\Api\SoCommunicator;
use Knusperleicht\EpsBankTransfer\Domain\BankConfirmationDetails;
use Knusperleicht\EpsBankTransfer\Domain\VitalityCheckDetails;
use Monolog\Logger;
use Nyholm\Psr7\Factory\Psr17Factory;
use Knusperleicht\EpsBankTransfer\Exceptions\EpsException;
use Symfony\Component\HttpClient\Psr18Client;

// This endpoint is called by the EPS Scheme Operator (SO):
// - First as a "vitality check" (pre-check) to verify your endpoint is reachable.
// - Later with the actual payment confirmation after the customer completes authorization at the bank.

$paymentConfirmationCallback = function (string $plainXml, BankConfirmationDetails $bankConfirmationDetails) {
    // Handle eps:StatusCode: 'OK', 'NOK', 'VOK' (validation OK), or 'UNKNOWN'
    if ($bankConfirmationDetails->getStatusCode() === 'OK') {
        // TODO: Mark the order as paid and proceed with fulfillment.
        // Prefer using a stable identifier you provided, e.g.:
        // - $bankConfirmationDetails->getRemittanceIdentifier()
        // - $bankConfirmationDetails->getUnstructuredRemittanceIdentifier()
        // Optionally log/inspect $plainXml for audits.
    }

    // Return true to acknowledge you received and processed the confirmation.
    // Returning false (or throwing) will signal to the SO that the confirmation was not accepted.
    return true;
};

$vitalityCheckCallback = function (string $plainXml, VitalityCheckDetails $vitalityCheckDetails) {
    // Return true to indicate your endpoint is healthy and reachable (EPS "VitalityCheck")
    return true;
};

// Load configuration
$config = file_exists(__DIR__ . '/config.local.php')
    ? require __DIR__ . '/config.local.php'
    : require __DIR__ . '/config.example.php';

try {
    $psr17Factory = new Psr17Factory();
    $soCommunicator = new SoCommunicator(
        new Psr18Client(),
        $psr17Factory,
        $psr17Factory,
        SoCommunicator::TEST_MODE_URL, // Use LIVE base URL in production
        new Logger('eps')
    );

    // Provide raw input/output streams. In production, keep these as shown.
    $soCommunicator->handleConfirmationUrl(
        $paymentConfirmationCallback,
        $vitalityCheckCallback,       // Vitality check callback
        'php://input',   // Raw POST body received from the SO
        'php://output',  // Raw output stream returned to the SO 
        $config['interface_version'] ?? null // Optional: omit to use default '2.6'
    );
} catch (EpsException $e) {
    echo 'EPS Exception: ' . $e->getMessage() . PHP_EOL;
    http_response_code(500);
} catch (\Exception $e) {
    echo 'Exception: ' . $e->getMessage() . PHP_EOL;
    http_response_code(500);
}
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
use Knusperleicht\EpsBankTransfer\Exceptions\EpsException;
use Knusperleicht\EpsBankTransfer\Requests\RefundRequest;
use Monolog\Logger;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\HttpClient\Psr18Client;

// Load configuration
$config = file_exists(__DIR__ . '/config.local.php')
    ? require __DIR__ . '/config.local.php'
    : require __DIR__ . '/config.example.php';

// === Refund configuration ===
$userID = $config['user_id'];               // EPS merchant (User) ID = epsr:UserId
$pin = $config['pin'];                      // Merchant PIN/secret used to compute SHA-256 fingerprint (epsr:SHA256Fingerprint)
$merchantIban = $config['merchant_iban'];   // Your merchant IBAN to receive/issue refunds

$refundRequest = new RefundRequest(
    date('Y-m-d\TH:i:s.vP'),  // Current timestamp (must not differ more than ~3 hours from SO time)
    $config['sample_refund_transaction_id'],        // EPS Transaction ID (from epsp:BankResponse of the original payment)
    $merchantIban,
    1,                  // Refund amount in cents (must be <= original amount)
    'EUR',                // Currency (EPS Refund 1.0.1 only accepts EUR)
    $userID,
    $pin,
    'Refund Reason'       // Optional RefundReference (Auftraggeberreferenz)
);


// === Send the refund request to the EPS Scheme Operator (SO) ===
$testMode = true;
$psr17Factory = new Psr17Factory();
$soCommunicator = new SoCommunicator(
    new Psr18Client(),
    $psr17Factory,
    $psr17Factory,
    SoCommunicator::TEST_MODE_URL,   // Use LIVE base URL in production
    new Logger('eps')
);

try {
    // Optional version can be passed as the 2nd argument; default is '2.6'
    $refundResponse = $soCommunicator->sendRefundRequest($refundRequest, '2.6'); //only 2.7 is supported at this time

    echo $refundResponse->getStatusCode() . ', ' . $refundResponse->getErrorMessage() . PHP_EOL;
    // Note: Status code '000' (No Errors) means the bank accepted the refund request.
    // Depending on the bank, manual approval might still be required.
} catch (EpsException $e) {
    echo 'EPS Exception: ' . $e->getMessage() . PHP_EOL;
} catch (\Exception $e) {
    echo 'Exception: ' . $e->getMessage() . PHP_EOL;
}
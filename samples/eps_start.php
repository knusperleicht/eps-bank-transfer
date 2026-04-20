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
require_once('../vendor/autoload.php');

// Optional: interface version can be set in samples/config.local.php (key: 'interface_version').
// If not provided, the library default will be used (currently '2.6').

use Knusperleicht\EpsBankTransfer\Api\SoCommunicator;
use Knusperleicht\EpsBankTransfer\Exceptions\EpsException;
use Knusperleicht\EpsBankTransfer\Requests\Parts\WebshopArticle;
use Knusperleicht\EpsBankTransfer\Requests\TransferInitiatorDetails;
use Knusperleicht\EpsBankTransfer\Requests\Parts\PaymentFlowUrls;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\HttpClient\Psr18Client;

// Load configuration
$config = file_exists(__DIR__ . '/config.local.php')
    ? require __DIR__ . '/config.local.php'
    : require __DIR__ . '/config.example.php';

// === Configure your merchant and beneficiary account details ===
$userID = $config['user_id'];            // EPS merchant User ID (epsp:UserId)
$pin    = $config['pin'];                // Merchant PIN/secret used to compute MD5 fingerprint (epsp:MD5Fingerprint)
$bic    = $config['beneficiary_bic'];    // BIC of the beneficiary account = epi:BfiBicIdentifier
$iban   = $config['beneficiary_iban'];   // IBAN of the beneficiary account = epi:BeneficiaryAccountIdentifier

// === URLs used during the payment flow ===
$paymentFlowUrls = new PaymentFlowUrls(
    // Confirmation URL: Called by the EPS Scheme Operator (SO) BEFORE (vitality check) and AFTER the customer authorizes payment.
    // IMPORTANT: Include a unique identifier in the query string (e.g., order ID) so you can match the callback to your order.
    // See samples/eps_confirm.php for handling.
    $config['confirmation_url'], // = epsp:ConfirmationUrl

    // Customer redirect on successful payment authorization.
    $config['ok_url'],            // = epsp:TransactionOkUrl

    // Customer redirect on cancellation or failure.
    $config['nok_url']              // = epsp:TransactionNokUrl
);

$initiateTransferRequest = new TransferInitiatorDetails(
    $userID,
    $pin,
    $bic,
    // Beneficiary name (and optional address). Spec allows up to 140 chars, but banks often only guarantee 70. No line breaks.
    $config['beneficiary_name'],                                            // = epi:BeneficiaryNameAddressText
    $iban,
    // Reference Identifier (mandatory by spec) but not returned in confirmation or shown on bank statement.
    // Best practice: reuse your order number (often equals the Remittance Identifier).
    '12345',                                                     // = epi:ReferenceIdentifier
    // Total amount in EURO cents (e.g., 9999 = €99.99).
    1000,                                                      // ≈ epi:InstructedAmount
    $paymentFlowUrls,
    null
);

// Optional: link timeout in minutes
$initiateTransferRequest->setExpirationMinutes(60);

// Optional: enable obscurity (hash suffix) if desired
//use Knusperleicht\EpsBankTransfer\Requests\Parts\ObscurityConfig;
//$initiateTransferRequest->setObscurityConfig(new ObscurityConfig(8, 'my-custom-seed'));

// Optional: Include ONE (not both!) of the following remittance fields.
// These values are returned in the confirmation and are useful for matching.
$initiateTransferRequest->setRemittanceIdentifier('Order123');                  // "Remittance Identifier" (Zahlungsreferenz) = epi:RemittanceIdentifier
// Only use ONE of the following fields. Commented out to follow spec strictly:
// $initiateTransferRequest->setUnstructuredRemittanceIdentifier('Order123');     // "Unstructured Remittance Identifier" (Verwendungszweck) = epi:UnstructuredRemittanceIdentifier

// Optional: Provide article details shown by some banks/webshops = epsp:WebshopDetails
$article = new WebshopArticle( // = epsp:WebshopArticle
    'ArticleName',   // Article name
    1,               // Quantity
    1000             // Unit price in EURO cents
);
$initiateTransferRequest->addArticle($article);

// === Send TransferInitiatorDetails to the EPS Scheme Operator (SO) ===
$testMode = true; // To use live mode, construct SoCommunicator with the LIVE base URL
$psr17Factory = new Psr17Factory();
$soCommunicator = new SoCommunicator(
    new Psr18Client(),
    $psr17Factory,
    $psr17Factory,
    SoCommunicator::TEST_MODE_URL, // Change to LIVE base URL for production
    new Monolog\Logger('eps')
);

// Optional: Display a bank selection to the user on your checkout page
// Example: Fetch current bank list (available for interface 2.6) and print BIC + name
try {
    $bankList = $soCommunicator->getBanks($config['interface_version'] ?? null);
    foreach ($bankList->getBanks() as $bank) {
        // You could render this as a dropdown in your checkout
        echo "BIC: " . $bank->getBic() . "\n";
        echo "Name: " . $bank->getName() . "\n";
        echo "URL: " . $bank->getUrl() . "\n";
        echo "Country: " . $bank->getCountryCode() . "\n";
        echo "National Payment Types: " . implode(', ', $bank->getNationalPaymentTypes()) . "\n";
        echo "International Payment Type: " . ($bank->getInternationalPaymentType() ?? 'N/A') . "\n";
        echo "App2App: " . ($bank->isApp2app() ? 'Yes' : 'No') . "\n";
        echo "-------------------\n";
    }

    // Example: Preselect a bank using orderingCustomerOfiIdentifier (per EPS spec v2.6)
    // Rather than overriding the endpoint URL directly, we set the customer's bank selection via 
    // orderingCustomerOfiIdentifier to ensure proper routing to the bank.
    // In your UI, let the customer select their bank; here we just take the first as a demo:
    $banks = $bankList->getBanks($config['interface_version'] ?? null);
    if (!empty($banks)) {
        // orderingCustomerOfiIdentifier expects the bank identifier (typically BIC).
        // Please select a bank from the list above and call:
        //$initiateTransferRequest->setOrderingCustomerOfiIdentifier($banks[0]->getBic());
    }
} catch (\Throwable $e) {
    // If bank list is temporarily unavailable, continue without preselection
    echo "Bank list: " . $e->getMessage() . "\n";
}

// Optional: Override the default base URL (rarely needed)
//$soCommunicator->setBaseUrl('https://example.com/My/Eps/Environment');

// Perform the initiate-transfer call and handle the response
try {
    // Optional version can be passed as the 2nd argument; default is '2.6'
    $protocolDetails = $soCommunicator->sendTransferInitiatorDetails(
        $initiateTransferRequest,
            $config['interface_version'] ?? null
    );

    if ($protocolDetails->getErrorCode() !== '000') {
        // Non-success from SO: log/display for troubleshooting
        $errorCode = $protocolDetails->getErrorCode();
        $errorMessage = $protocolDetails->getErrorMessage();
        $transactionId = $protocolDetails->getTransactionId();

        echo "Error occurred during EPS bank transfer initiation:\n";
        echo "Error code: " . $errorCode . "\n";
        echo "Error message: " . $errorMessage . "\n";
    } else {
        // Redirect the customer to their bank to complete authorization
        echo "Redirecting to client URL: " . $protocolDetails->getClientRedirectUrl() . "\n";
        echo "Transaction ID: " . $protocolDetails->getTransactionId() . "\n";

        //usually you want to redirect the customer to the client URL
        //header('Location: ' . $protocolDetails->getClientRedirectUrl());
    }
} catch (EpsException $e) {
    // Handle PSA specific exceptions
    $errorCode = 'EPS Exception';
    $errorMessage = $e->getMessage();
    echo "Error occurred during EPS bank transfer initiation:\n";
    echo "Error code: " . $errorCode . "\n";
    echo "Error message: " . $errorMessage . "\n";
} catch (\Exception $e) {
    // Handle other unexpected exceptions
    $errorCode = 'Exception';
    $errorMessage = $e->getMessage();
    echo "Error occurred during EPS bank transfer initiation:\n";
    echo "Error code: " . $errorCode . "\n";
    echo "Error message: " . $errorMessage . "\n";
}
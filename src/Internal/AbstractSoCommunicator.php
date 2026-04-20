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

namespace Knusperleicht\EpsBankTransfer\Internal;

use Exception;
use JMS\Serializer\SerializerInterface;
use Knusperleicht\EpsBankTransfer\Domain\BankConfirmationDetails;
use Knusperleicht\EpsBankTransfer\Domain\VitalityCheckDetails;
use Knusperleicht\EpsBankTransfer\Exceptions\CallbackResponseException;
use Knusperleicht\EpsBankTransfer\Exceptions\EpsException;
use Knusperleicht\EpsBankTransfer\Exceptions\InvalidCallbackException;
use Knusperleicht\EpsBankTransfer\Exceptions\XmlValidationException;
use Knusperleicht\EpsBankTransfer\Internal\Generated\BankList\EpsSOBankListProtocol;
use Knusperleicht\EpsBankTransfer\Responses\ShopResponseDetails;
use Knusperleicht\EpsBankTransfer\Utilities\XmlValidator;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Abstract base for versioned internal communicators (v2.6, v2.7).
 *
 * Holds shared HTTP/serialization core and provides common implementations
 * for sending TransferInitiator requests and handling Confirmation/Vitality
 * callbacks. Concrete subclasses must provide version-specific hooks.
 */
abstract class AbstractSoCommunicator
{
    /** @var SoCommunicatorCore */
    protected $core;
    /** @var SerializerInterface */
    protected $serializer;

    /**
     * Constructs a new SO communicator instance.
     *
     * @param ClientInterface $httpClient HTTP client for making requests
     * @param RequestFactoryInterface $requestFactory Factory for creating PSR-7 requests
     * @param StreamFactoryInterface $streamFactory Factory for creating PSR-7 streams
     * @param string $baseUrl Base URL for all API endpoints
     * @param LoggerInterface|null $logger Optional logger for debug output
     */
    public function __construct(
        ClientInterface         $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface  $streamFactory,
        string                  $baseUrl,
        LoggerInterface         $logger = null
    )
    {
        $this->core = new SoCommunicatorCore(
            $httpClient,
            $requestFactory,
            $streamFactory,
            $baseUrl,
            $logger
        );
        $this->serializer = $this->core->getSerializer();
    }

    /**
     * Send a TransferInitiatorDetails request to EPS endpoint for this version.
     *
     * @param mixed $transferInitiatorDetails Domain request (type differs per version namespace)
     * @param string|null $targetUrl Optional endpoint override
     * @return object Deserialized protocol response object (generated class per version)
     * @throws XmlValidationException
     * @throws Exception
     */
    public function sendTransferInitiatorDetails($transferInitiatorDetails, ?string $targetUrl = null): object
    {
        $this->core->handleObscurityConfig($transferInitiatorDetails);

        $targetUrl = $targetUrl ?? $this->core->getBaseUrl() . $this->getTransferPath();

        $xmlData = $this->serializeTransferInitiator($transferInitiatorDetails);
        $response = $this->core->postUrl($targetUrl, $xmlData, 'Send payment order (' . $this->getVersion() . ')');

        XmlValidator::validateEpsProtocol($response, $this->getVersion());

        $protocolClass = $this->protocolClassFqn();
        return $this->serializer->deserialize($response, $protocolClass, 'xml');
    }

    /**
     * Handle incoming EPS confirmation/vitality callback request (shared flow).
     *
     * @param callable|null $confirmationCallback Callback for handling bank confirmations
     * @param callable|null $vitalityCheckCallback Callback for handling vitality checks
     * @param string $rawPostStream Stream to read raw POST data from
     * @param string $outputStream Stream to write response to
     * @throws InvalidCallbackException If callbacks are invalid
     * @throws XmlValidationException If XML is invalid
     * @throws CallbackResponseException If callback response is invalid
     */
    public function handleConfirmationUrl(
        $confirmationCallback = null,
        $vitalityCheckCallback = null,
        string $rawPostStream = 'php://input',
        string $outputStream = 'php://output'
    ): void
    {
        try {
            if ($confirmationCallback === null || !is_callable($confirmationCallback)) {
                throw new InvalidCallbackException('ConfirmationCallback not callable or missing');
            }
            if ($vitalityCheckCallback !== null && !is_callable($vitalityCheckCallback)) {
                throw new InvalidCallbackException('VitalityCheckCallback not callable');
            }

            $rawXml = file_get_contents($rawPostStream);
            XmlValidator::validateEpsProtocol($rawXml, $this->getVersion());

            $protocol = $this->serializer->deserialize($rawXml, $this->protocolClassFqn(), 'xml');

            $vitality = $this->vitalityFromProtocol($protocol);
            if ($vitality !== null) {
                $this->handleVitalityCheck($vitalityCheckCallback, $rawXml, $vitality, $outputStream);
                return;
            }

            $confirmation = $this->bankConfirmationFromProtocol($protocol);
            if ($confirmation !== null) {
                $this->handleBankConfirmation($confirmationCallback, $rawXml, $confirmation, $outputStream);
                return;
            }

            throw new XmlValidationException('Unknown confirmation details structure');
        } catch (Exception $e) {
            $this->handleException($e, $outputStream);
            throw $e;
        }
    }

    /**
     * Handle vitality check callback.
     *
     * @param callable|null $callback Callback function to handle vitality check
     * @param string $rawXml Raw XML from request
     * @param VitalityCheckDetails $vitality Parsed vitality check details
     * @param string $outputStream Stream to write response to
     * @throws CallbackResponseException If callback doesn't return true
     */
    protected function handleVitalityCheck(?callable $callback, string $rawXml, VitalityCheckDetails $vitality, string $outputStream): void
    {
        if ($callback !== null) {
            if (call_user_func($callback, $rawXml, $vitality) !== true) {
                throw new CallbackResponseException('Vitality check callback must return true');
            }
        }
        file_put_contents($outputStream, $rawXml);
    }

    /**
     * Handle bank confirmation callback.
     *
     * @param callable $callback Callback function to handle bank confirmation
     * @param string $rawXml Raw XML from request
     * @param BankConfirmationDetails $confirmation Parsed bank confirmation details
     * @param string $outputStream Stream to write response to
     * @throws CallbackResponseException If callback doesn't return true
     */
    protected function handleBankConfirmation(callable $callback, string $rawXml, BankConfirmationDetails $confirmation, string $outputStream): void
    {
        $shopConfirmationDetails = new ShopResponseDetails();
        $shopConfirmationDetails->setSessionId($confirmation->getSessionId());
        $shopConfirmationDetails->setStatusCode($confirmation->getStatusCode());
        $shopConfirmationDetails->setPaymentReferenceIdentifier($confirmation->getPaymentReferenceIdentifier());

        if (call_user_func($callback, $rawXml, $confirmation) !== true) {
            throw new CallbackResponseException('Confirmation callback must return true');
        }

        $xml = $this->shopResponseXml($shopConfirmationDetails);
        file_put_contents($outputStream, $xml);
    }

    /**
     * Handle exceptions during confirmation processing.
     *
     * @param Exception $e Exception that occurred
     * @param string $outputStream Stream to write error response to
     */
    protected function handleException(Exception $e, string $outputStream): void
    {
        $shopConfirmationDetails = new ShopResponseDetails();

        if ($e instanceof EpsException) {
            $shopConfirmationDetails->setErrorMessage($e->getMessage());
        } else {
            $shopConfirmationDetails->setErrorMessage('Exception "' . get_class($e) . '" occurred during confirmation handling');
        }

        file_put_contents($outputStream, $this->shopResponseXml($shopConfirmationDetails));
    }

    /**
     * Retrieve the EPS bank list (shared for all versions).
     *
     * @param string|null $targetUrl Optional override of the bank list endpoint
     * @return EpsSOBankListProtocol Parsed list of SO banks
     * @throws XmlValidationException When response XML is not valid
     */
    public function getBanks(?string $targetUrl = null): EpsSOBankListProtocol
    {
        $targetUrl = $targetUrl ?? $this->core->getBaseUrl() . $this->getBankListPath();
        $body = $this->core->getUrl($targetUrl, 'Requesting bank list');

        XmlValidator::validateBankList($body);

        return $this->serializer->deserialize($body, EpsSOBankListProtocol::class, 'xml');
    }

    /**
     * Get the version string for this communicator.
     *
     * @return string Version identifier (e.g. "2.6")
     */
    abstract protected function getVersion(): string;

    /**
     * Get the path for transfer initiator endpoint.
     *
     * @return string URL path
     */
    abstract protected function getTransferPath(): string;

    /**
     * Get the path for bank list endpoint.
     *
     * @return string URL path
     */
    abstract protected function getBankListPath(): string;

    /**
     * Get the fully qualified class name for protocol details.
     *
     * @return string FQCN of protocol details class
     */
    abstract protected function protocolClassFqn(): string;

    /**
     * Serialize transfer initiator details to XML.
     *
     * @param mixed $transferInitiatorDetails Request details object
     * @return string Serialized XML
     */
    abstract protected function serializeTransferInitiator($transferInitiatorDetails): string;

    /**
     * Extract vitality check details from protocol.
     *
     * @param mixed $protocol Protocol details object
     * @return VitalityCheckDetails|null Extracted details or null if not present
     */
    abstract protected function vitalityFromProtocol($protocol): ?VitalityCheckDetails;

    /**
     * Extract bank confirmation details from protocol.
     *
     * @param mixed $protocol Protocol details object
     * @return BankConfirmationDetails|null Extracted details or null if not present
     */
    abstract protected function bankConfirmationFromProtocol($protocol): ?BankConfirmationDetails;

    /**
     * Generate XML response for shop confirmation.
     *
     * @param ShopResponseDetails $details Response details
     * @return string Generated XML
     */
    abstract protected function shopResponseXml(ShopResponseDetails $details): string;
}
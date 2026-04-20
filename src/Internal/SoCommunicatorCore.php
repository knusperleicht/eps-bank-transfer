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

use InvalidArgumentException;
use JMS\Serializer\SerializerInterface;
use Knusperleicht\EpsBankTransfer\Exceptions\UnknownRemittanceIdentifierException;
use Knusperleicht\EpsBankTransfer\Requests\TransferInitiatorDetails;
use Knusperleicht\EpsBankTransfer\Serializer\SerializerFactory;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use UnexpectedValueException;

class SoCommunicatorCore
{
    /** @var ClientInterface */
    private $httpClient;

    /** @var RequestFactoryInterface */
    private $requestFactory;

    /** @var StreamFactoryInterface */
    private $streamFactory;

    /** @var SerializerInterface */
    private $serializer;

    /** @var LoggerInterface|null */
    private $logger;

    /** @var string */
    private $baseUrl;

    public function __construct(
        ClientInterface         $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface  $streamFactory,
        string                  $baseUrl,
        ?LoggerInterface        $logger = null
    )
    {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->baseUrl = $baseUrl;
        $this->logger = $logger;
        $this->serializer = SerializerFactory::create();
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Execute a GET request and return the raw response body.
     *
     * Adds Accept headers for XML responses and throws a RuntimeException on
     * transport errors or non-2xx status codes.
     *
     * @param string $url Fully-qualified URL to request.
     * @param string|null $logMessage Optional message for info logs.
     * @return string Raw response body.
     */
    public function getUrl(string $url, string $logMessage = null): string
    {
        $this->logInfo($logMessage ?: 'GET ' . $url);

        try {
            $request = $this->requestFactory->createRequest('GET', $url)
                ->withHeader('Accept', 'application/xml,text/xml,*/*');

            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            $this->logError('GET failed: ' . $e->getMessage());
            throw new RuntimeException('GET request failed: ' . $e->getMessage(), 0, $e);
        }

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            $this->logError(sprintf('GET %s failed with HTTP %d', $url, $response->getStatusCode()));
            throw new RuntimeException(sprintf('GET %s failed with HTTP %d', $url, $response->getStatusCode()));
        }

        $this->logInfo('GET success: ' . $url);
        return (string)$response->getBody();
    }

    /**
     * Execute a POST request with XML body and return the raw response body.
     *
     * Sets appropriate XML headers and throws a RuntimeException on transport
     * errors or non-2xx status codes.
     *
     * @param string $url Endpoint URL.
     * @param string $xmlBody XML payload to send.
     * @param string|null $logMessage Optional message for info logs.
     * @return string Raw response body.
     */
    public function postUrl(string $url, string $xmlBody, string $logMessage = null): string
    {
        $this->logInfo($logMessage ?: 'POST ' . $url);

        try {
            $stream = $this->streamFactory->createStream($xmlBody);

            $request = $this->requestFactory->createRequest('POST', $url)
                ->withHeader('Content-Type', 'application/xml; charset=UTF-8')
                ->withHeader('Accept', 'application/xml,text/xml,*/*')
                ->withBody($stream);

            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            $this->logError('POST failed: ' . $e->getMessage());
            throw new RuntimeException('POST request failed: ' . $e->getMessage(), 0, $e);
        }

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            $this->logError(sprintf('POST %s failed with HTTP %d', $url, $response->getStatusCode()));
            throw new RuntimeException(sprintf('POST %s failed with HTTP %d', $url, $response->getStatusCode()));
        }

        $this->logInfo('POST success: ' . $url);
        return (string)$response->getBody();
    }

    /**
     * Append a deterministic hash suffix to a string for obscurity purposes.
     *
     * If obscuritySuffixLength is 0, the original string is returned. When a
     * non-zero length is provided, obscuritySeed must be set; otherwise an
     * UnexpectedValueException is thrown.
     *
     * @param string $string Base string.
     * @param int $obscuritySuffixLength Number of characters from the hash to append.
     * @param string|null $obscuritySeed Secret seed for hashing.
     * @return string Suffix-augmented string.
     */
    public function appendHash(string $string, int $obscuritySuffixLength = 0, ?string $obscuritySeed = null): string
    {
        if ($obscuritySuffixLength === 0) {
            return $string;
        }

        if (empty($obscuritySeed)) {
            throw new UnexpectedValueException('No security seed set when using security suffix.');
        }

        $hash = hash('sha256', $string . $obscuritySeed);
        return $string . substr($hash, 0, $obscuritySuffixLength);
    }

    /**
     * Remove and verify an obscurity hash suffix from the given string.
     *
     * If obscuritySuffixLength is 0, the input string is returned unchanged.
     * Otherwise, the suffix is stripped and re-computed for verification; if it
     * does not match, an UnknownRemittanceIdentifierException is thrown.
     *
     * @param string $suffixed Input string which may have a hash suffix.
     * @param int $obscuritySuffixLength Length of the suffix to strip.
     * @param string|null $obscuritySeed Secret seed used for hashing.
     * @return string Original string without suffix.
     * @throws UnknownRemittanceIdentifierException When verification fails.
     */
    public function stripHash(string $suffixed, int $obscuritySuffixLength = 0, ?string $obscuritySeed = null): string
    {
        if ($obscuritySuffixLength === 0) {
            return $suffixed;
        }

        $remittanceIdentifier = substr($suffixed, 0, -$obscuritySuffixLength);

        if ($this->appendHash($remittanceIdentifier, $obscuritySuffixLength, $obscuritySeed) !== $suffixed) {
            throw new UnknownRemittanceIdentifierException(
                'Unknown RemittanceIdentifier supplied: ' . $suffixed
            );
        }

        return $remittanceIdentifier;
    }

    /**
     * Apply obscurity (hash suffix) rules to remittance identifiers in the request.
     *
     * Ensures total length constraints (35 characters for RemittanceIdentifier,
     * 140 for UnstructuredRemittanceIdentifier) before appending an optional
     * hash suffix based on provided ObscurityConfig.
     *
     * @param TransferInitiatorDetails $transferInitiatorDetails The request to mutate in-place.
     */
    public function handleObscurityConfig(TransferInitiatorDetails $transferInitiatorDetails): void
    {
        $cfg = $transferInitiatorDetails->getObscurityConfig();
        $suffixLength = $cfg ? $cfg->getLength() : 0;
        $seed = $cfg ? $cfg->getSeed() : null;

        if ($transferInitiatorDetails->getRemittanceIdentifier() !== null) {
            $base = $transferInitiatorDetails->getRemittanceIdentifier();
            if ($suffixLength > 0 && strlen($base) + $suffixLength > 35) {
                throw new InvalidArgumentException('RemittanceIdentifier too long for configured obscurity length. Max total 35 characters.');
            }
            $transferInitiatorDetails->setRemittanceIdentifier(
                $this->appendHash(
                    $base,
                    $suffixLength,
                    $seed,
                )
            );
        }

        if ($transferInitiatorDetails->getUnstructuredRemittanceIdentifier() !== null) {
            $base = $transferInitiatorDetails->getUnstructuredRemittanceIdentifier();
            if ($suffixLength > 0 && strlen($base) + $suffixLength > 140) {
                throw new InvalidArgumentException('UnstructuredRemittanceIdentifier too long for configured obscurity length. Max total 140 characters.');
            }
            $transferInitiatorDetails->setUnstructuredRemittanceIdentifier(
                $this->appendHash(
                    $base,
                    $suffixLength,
                    $seed
                )
            );
        }
    }

    private function logInfo(string $message): void
    {
        if ($this->logger !== null) {
            $this->logger->info('[EPS] ' . $message);
        }
    }

    private function logError(string $message): void
    {
        if ($this->logger !== null) {
            $this->logger->error('[EPS] ' . $message);
        }
    }

    /**
     * Get the configured JMS Serializer instance used for XML (de)serialization.
     *
     * @return SerializerInterface
     */
    public function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }
}

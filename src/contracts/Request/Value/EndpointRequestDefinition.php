<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Test\Rest\Request\Value;

use Ibexa\Contracts\Test\Rest\Input\Value\InputPayload;
use Stringable;

final class EndpointRequestDefinition implements Stringable
{
    public const string DEFAULT_FORMAT = 'xml';

    /**
     * @param array<string, string> $headers input headers
     * @param string|null $name unique name, used to generate PHPUnit data set name
     * @param string|null $snapshotName Snapshot name or path relative to Snapshot directory defined by overriding {@see \Ibexa\Contracts\Test\Rest\BaseRestWebTestCase::getSnapshotDirectory()}
     * @param int|null $expectedStatusCode expected HTTP status code. If none is given, any successful status code is accepted (>=200 <300)
     */
    public function __construct(
        private readonly string $method,
        private readonly string $uri,
        private readonly ?string $expectedResourceType,
        private ?string $acceptHeader = null,
        private readonly array $headers = [],
        private ?InputPayload $payload = null,
        private readonly ?string $name = null,
        private ?string $snapshotName = null,
        private ?int $expectedStatusCode = null
    ) {
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getExpectedResourceType(): ?string
    {
        return $this->expectedResourceType;
    }

    public function getAcceptHeader(): ?string
    {
        return $this->acceptHeader;
    }

    public function __toString(): string
    {
        return $this->name
            ?? sprintf(
                '%s %s accepting %s %s',
                $this->method,
                $this->uri,
                $this->getFormatDescription(),
                $this->getPayloadDescription()
            );
    }

    private function getPayloadDescription(): string
    {
        return null !== $this->payload
            ? sprintf('with %s payload', $this->payload)
            : 'without payload';
    }

    private function getFormatDescription(): string
    {
        $format = $this->extractFormatFromAcceptHeader();

        return "$format format";
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getPayload(): ?InputPayload
    {
        return $this->payload;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return 'xml'|'json'
     */
    public function extractFormatFromAcceptHeader(): string
    {
        if (null === $this->acceptHeader) {
            return self::DEFAULT_FORMAT;
        }
        if (preg_match('#application/.*\+?(xml|json)#i', $this->acceptHeader, $matches)) {
            /** @var 'xml'|'json' */
            return strtolower($matches[1]);
        }

        throw new \RuntimeException(
            'Format can be either xml or json. The given accept header: ' . $this->acceptHeader
        );
    }

    public function getSnapshotName(): ?string
    {
        return $this->snapshotName;
    }

    public function getExpectedStatusCode(): ?int
    {
        return $this->expectedStatusCode;
    }

    public function withAcceptHeader(?string $acceptHeader): self
    {
        $endpointDefinition = clone $this;
        $endpointDefinition->acceptHeader = $acceptHeader;

        return $endpointDefinition;
    }

    public function withPayload(?InputPayload $payload): self
    {
        $endpointDefinition = clone $this;
        $endpointDefinition->payload = $payload;

        return $endpointDefinition;
    }

    public function withSnapshotName(?string $snapshotName): self
    {
        $endpointDefinition = clone $this;
        $endpointDefinition->snapshotName = $snapshotName;

        return $endpointDefinition;
    }

    public function withExpectedStatusCode(?int $expectedStatusCode): self
    {
        $endpointDefinition = clone $this;
        $endpointDefinition->expectedStatusCode = $expectedStatusCode;

        return $endpointDefinition;
    }

    public function __clone()
    {
        if (null !== $this->payload) {
            $this->payload = clone $this->payload;
        }
    }
}

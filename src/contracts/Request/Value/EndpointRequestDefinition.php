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
    private string $method;

    private string $uri;

    private ?string $resourceType;

    /** @var array<string, string> */
    private array $headers;

    private ?InputPayload $payload;

    private ?string $name;

    /** @phpstan-var 'xml'|'json'|null  */
    private ?string $format;

    /**
     * @param array<string, string> $headers input headers
     * @param string|null $name unique name
     *
     * @phpstan-param 'xml'|'json' $format
     */
    public function __construct(
        string $method,
        string $uri,
        ?string $resourceType,
        array $headers = [],
        ?InputPayload $payload = null,
        ?string $name = null,
        ?string $format = null
    ) {
        $this->method = $method;
        $this->uri = $uri;
        $this->resourceType = $resourceType;
        $this->headers = $headers;
        $this->payload = $payload;
        $this->name = $name;
        $this->format = $format;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getResourceType(): ?string
    {
        return $this->resourceType;
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
        return null !== $this->format ? "$this->format format" : 'no format';
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

    public function getFormat(): ?string
    {
        return $this->format;
    }

    /**
     * @phpstan-param 'xml'|'json' $format
     */
    public function withFormat(string $format): self
    {
        $endpointDefinition = clone $this;
        $endpointDefinition->format = $format;

        return $endpointDefinition;
    }

    public function __clone()
    {
        if (null !== $this->payload) {
            $this->payload = clone $this->payload;
        }
    }
}

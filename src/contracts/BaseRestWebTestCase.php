<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Test\Rest;

use Ibexa\Contracts\Test\Rest\Input\Value\InputPayload;
use Ibexa\Contracts\Test\Rest\Request\Value\EndpointRequestDefinition;
use Ibexa\Contracts\Test\Rest\Schema\ValidatorInterface;
use Ibexa\Test\Rest\Schema\Validator\SchemaValidatorRegistry;

abstract class BaseRestWebTestCase extends WebTestCase
{
    public const REQUIRED_FORMATS = ['xml', 'json'];

    abstract protected function getExpectedResourceType(): ?string;

    /**
     * @return iterable<\Ibexa\Contracts\Test\Rest\Request\Value\EndpointRequestDefinition>
     */
    abstract public static function getEndpointsToTest(): iterable;

    /**
     * @dataProvider getEndpointsData
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\Exception
     * @throws \JsonException
     */
    public function testEndpoint(EndpointRequestDefinition $endpointDefinition): void
    {
        $method = $endpointDefinition->getMethod();
        $uri = $endpointDefinition->getUri();

        /** @phpstan-var 'xml'|'json' $format */
        $format = $endpointDefinition->getFormat();
        self::assertContains($format, self::REQUIRED_FORMATS, 'Unknown format for ' . $endpointDefinition);

        $this->assertRequestResponseFormat(
            $format,
            $method,
            $uri,
            $endpointDefinition->getHeaders(),
            $endpointDefinition->getPayload()
        );
    }

    /**
     * @return iterable<string, array<\Ibexa\Contracts\Test\Rest\Request\Value\EndpointRequestDefinition>>
     */
    final public static function getEndpointsData(): iterable
    {
        foreach (static::getEndpointsToTest() as $endpoint) {
            yield from self::buildEndpointsWithRequiredFormats($endpoint);
        }
    }

    /**
     * @return iterable<string, array<\Ibexa\Contracts\Test\Rest\Request\Value\EndpointRequestDefinition>>
     */
    private static function buildEndpointsWithRequiredFormats(EndpointRequestDefinition $endpoint): iterable
    {
        foreach (self::REQUIRED_FORMATS as $format) {
            $endpointWithFormat = $endpoint->withFormat($format);
            // create data set name using EndpointDefinition::__toString
            yield (string)$endpointWithFormat => [$endpointWithFormat];
        }
    }

    /**
     * @phpstan-param 'xml'|'json' $format
     *
     * @param array<string, string> $headers
     *
     * @throws \JsonException
     */
    protected function assertRequestResponseFormat(
        string $format,
        string $method,
        string $uri,
        array $headers,
        ?InputPayload $payload
    ): void {
        $resourceType = $this->getExpectedResourceType();

        if (null !== $resourceType) {
            $headers['HTTP_ACCEPT'] = $this->generateMediaTypeString("$resourceType+$format");
        } else {
            $headers['HTTP_ACCEPT'] = "application/$format";
        }

        if (null !== $payload) {
            $headers['CONTENT_TYPE'] = $this->generateMediaTypeString($payload->getMediaTypeWithFormat());
        }

        $this->client->request(
            $method,
            $uri,
            [],
            [],
            $headers,
            null !== $payload ? $payload->getContent() : null
        );

        self::assertResponseIsSuccessful();

        $this->assertResponseIsValid(
            (string)$this->client->getResponse()->getContent(),
            $resourceType,
            $format
        );
    }

    /**
     * @phpstan-param 'xml'|'json' $format
     *
     * @throws \JsonException
     */
    protected function assertResponseIsValid(
        string $response,
        ?string $resourceType,
        string $format
    ): void {
        self::assertIsString($response);

        if (null !== $resourceType) {
            self::assertStringContainsString($resourceType, $response);
            self::assertResponseHeaderSame(
                'Content-Type',
                $this->generateMediaTypeString("$resourceType+$format")
            );

            $this->validateAgainstSchema($response, $resourceType, $format);
        } else {
            self::assertEmpty($response, "The response for '$format' format is not empty");
        }
    }

    protected function generateMediaTypeString(string $typeString): string
    {
        return 'application/vnd.ibexa.api.' . $typeString;
    }

    private function validateAgainstSchema(
        string $response,
        string $resourceType,
        string $format
    ): void {
        $validator = $this->getSchemaValidator($format);
        $validator->validate($response, $resourceType);
    }

    private function getSchemaValidator(string $format): ValidatorInterface
    {
        /** @var \Ibexa\Test\Rest\Schema\Validator\SchemaValidatorRegistry $registry */
        $registry = self::getContainer()->get(SchemaValidatorRegistry::class);

        return $registry->getValidator($format);
    }
}

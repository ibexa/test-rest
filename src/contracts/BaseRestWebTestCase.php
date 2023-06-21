<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Test\Rest;

use Ibexa\Contracts\Test\Rest\Request\Value\EndpointRequestDefinition;
use Ibexa\Contracts\Test\Rest\Schema\ValidatorInterface;
use Ibexa\Test\Rest\Schema\Validator\SchemaValidatorRegistry;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseRestWebTestCase extends WebTestCase
{
    public const REQUIRED_FORMATS = ['xml', 'json'];

    abstract protected function getSchemaFileBasePath(string $resourceType, string $format): string;

    protected static function getSnapshotDirectory(): ?string
    {
        return null;
    }

    /**
     * @return iterable<\Ibexa\Contracts\Test\Rest\Request\Value\EndpointRequestDefinition>
     */
    protected static function getEndpointsToTest(): iterable
    {
        yield from [];

        self::fail(sprintf('%s needs to implement %s method', static::class, __METHOD__));
    }

    /**
     * @dataProvider getEndpointsData
     */
    public function testEndpoint(EndpointRequestDefinition $endpointDefinition): void
    {
        /** @phpstan-var 'xml'|'json' $format */
        $format = $endpointDefinition->getFormat();
        self::assertContains($format, self::REQUIRED_FORMATS, 'Unknown format for ' . $endpointDefinition);

        $response = $this->performRequest($endpointDefinition);

        self::assertResponseIsSuccessful();

        $content = (string)$response->getContent();
        $this->assertResponseIsValid(
            $content,
            $endpointDefinition->getResourceType(),
            $format
        );

        $snapshotName = $endpointDefinition->getSnapshotName();
        if (null !== $snapshotName) {
            $snapshotDirectory = static::getSnapshotDirectory();
            self::assertNotNull(
                $snapshotDirectory,
                sprintf(
                    'Tried to load %s.%s, but snapshot directory was not defined. Override getSnapshotDirectory method',
                    $snapshotName,
                    $format
                )
            );
            self::assertStringMatchesSnapshot($content, $format, "$snapshotDirectory/$snapshotName.$format");
        }
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

    protected function performRequest(EndpointRequestDefinition $endpointDefinition): Response
    {
        $method = $endpointDefinition->getMethod();
        $uri = $endpointDefinition->getUri();
        $resourceType = $endpointDefinition->getResourceType();
        $format = $endpointDefinition->getFormat();
        $headers = $endpointDefinition->getHeaders();

        if (null !== $resourceType) {
            $headers['HTTP_ACCEPT'] = $this->generateMediaTypeString("$resourceType+$format");
        } else {
            $headers['HTTP_ACCEPT'] = "application/$format";
        }

        if (null !== $endpointDefinition->getPayload()) {
            $headers['CONTENT_TYPE'] = $this->generateMediaTypeString(
                $endpointDefinition->getPayload()->getMediaTypeWithFormat()
            );
        }

        $this->client->request(
            $method,
            $uri,
            [],
            [],
            $headers,
            null !== $endpointDefinition->getPayload() ? $endpointDefinition->getPayload()->getContent() : null
        );

        return $this->client->getResponse();
    }

    /**
     * @phpstan-param 'xml'|'json' $format
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
        $validator->validate($response, $this->getSchemaFileBasePath($resourceType, $format));
    }

    private function getSchemaValidator(string $format): ValidatorInterface
    {
        /** @var \Ibexa\Test\Rest\Schema\Validator\SchemaValidatorRegistry $registry */
        $registry = self::getContainer()->get(SchemaValidatorRegistry::class);

        return $registry->getValidator($format);
    }
}

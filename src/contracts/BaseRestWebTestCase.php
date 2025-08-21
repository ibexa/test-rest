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
    public const array REQUIRED_FORMATS = ['xml', 'json'];

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
        $response = $this->performRequest($endpointDefinition);

        $expectedStatusCode = $endpointDefinition->getExpectedStatusCode();
        if (null === $expectedStatusCode) {
            self::assertResponseIsSuccessful();
        } else {
            $actualStatusCode = $response->getStatusCode();
            self::assertSame(
                $actualStatusCode,
                $expectedStatusCode,
                "Expected HTTP $expectedStatusCode, got HTTP $actualStatusCode status code"
            );
        }

        $content = (string)$response->getContent();
        $this->assertResponseIsValid(
            $content,
            $endpointDefinition->getExpectedResourceType(),
            $endpointDefinition->extractFormatFromAcceptHeader()
        );

        $snapshotName = $endpointDefinition->getSnapshotName();
        if (null !== $snapshotName) {
            $this->assertResponseMatchesSnapshot(
                $snapshotName,
                $endpointDefinition->extractFormatFromAcceptHeader(),
                $content
            );
        }
    }

    /**
     * @return iterable<string, array<\Ibexa\Contracts\Test\Rest\Request\Value\EndpointRequestDefinition>>
     */
    final public static function getEndpointsData(): iterable
    {
        foreach (static::getEndpointsToTest() as $endpoint) {
            // create data set name using EndpointDefinition::__toString
            yield (string)$endpoint => [$endpoint];
        }
    }

    protected function performRequest(EndpointRequestDefinition $endpointDefinition): Response
    {
        $method = $endpointDefinition->getMethod();
        $uri = $endpointDefinition->getUri();
        $headers = $endpointDefinition->getHeaders();

        $acceptHeader = $endpointDefinition->getAcceptHeader();
        if (null !== $acceptHeader) {
            $headers['HTTP_ACCEPT'] = $acceptHeader;
        }

        $inputPayload = $endpointDefinition->getPayload();
        if (null !== $inputPayload) {
            $headers['CONTENT_TYPE'] = self::generateMediaTypeString(
                $inputPayload->getMediaType(),
                $inputPayload->getFormat()
            );
        }

        $this->client->request(
            $method,
            $uri,
            [],
            [],
            $headers,
            null !== $inputPayload ? $inputPayload->getContent() : null
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
        if (null !== $resourceType) {
            self::assertStringContainsString($resourceType, $response);
            self::assertResponseHeaderSame(
                'Content-Type',
                self::generateMediaTypeString($resourceType, $format)
            );

            $this->validateAgainstSchema($response, $resourceType, $format);
        } else {
            self::assertEmpty($response, "The response for '$format' format is not empty");
        }
    }

    /**
     * @param 'xml'|'json' $format
     */
    private function assertResponseMatchesSnapshot(string $snapshotName, string $format, string $content): void
    {
        $snapshotDirectory = static::getSnapshotDirectory();
        self::assertNotNull(
            $snapshotDirectory,
            sprintf(
                'Tried to load %s.%s, but snapshot directory was not defined. Override getSnapshotDirectory method',
                $snapshotName,
                $format
            )
        );
        self::assertStringMatchesSnapshot(
            $content,
            $format,
            sprintf(
                '%s/%s.%s',
                $snapshotDirectory,
                $snapshotName,
                $format
            )
        );
    }

    protected static function generateMediaTypeString(string $typeString, ?string $formatSuffix = null): string
    {
        if (null !== $formatSuffix) {
            $typeString .= "+$formatSuffix";
        }

        return 'application/vnd.ibexa.api.' . $typeString;
    }

    private function validateAgainstSchema(
        string $response,
        string $resourceType,
        string $format
    ): void {
        try {
            $validator = $this->getSchemaValidator($format);
            $validator->validate($response, $this->getSchemaFileBasePath($resourceType, $format));
        } catch (\Throwable $e) {
            self::fail(
                sprintf(
                    'Failed to validate against schema the response of Media Type %s in %s format. ' .
                    "The response was:\n%s\n" .
                    "The exception was:\n%s\n",
                    $resourceType,
                    $format,
                    $response,
                    $e
                )
            );
        }
    }

    private function getSchemaValidator(string $format): ValidatorInterface
    {
        /** @var \Ibexa\Test\Rest\Schema\Validator\SchemaValidatorRegistry $registry */
        $registry = self::getContainer()->get(SchemaValidatorRegistry::class);

        return $registry->getValidator($format);
    }
}

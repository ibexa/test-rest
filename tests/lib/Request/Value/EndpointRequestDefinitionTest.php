<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Test\Rest\Request\Value;

use Ibexa\Contracts\Test\Rest\Request\Value\EndpointRequestDefinition;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \Ibexa\Contracts\Test\Rest\Request\Value\EndpointRequestDefinition
 */
final class EndpointRequestDefinitionTest extends TestCase
{
    /**
     * @return iterable<string, array{string|null, 'xml'|'json'}>
     */
    public function getDataForTestExtractFormatFromAcceptHeader(): iterable
    {
        yield 'application/json' => ['application/json', 'json'];
        yield 'application/xml' => ['application/xml', 'xml'];
        yield 'application/vnd.ibexa.api.Foo+xml' => ['application/vnd.ibexa.api.Foo+xml', 'xml'];
        yield 'application/vnd.ibexa.api.Foo+JSON' => ['application/vnd.ibexa.api.Foo+JSON', 'json'];
        yield 'application/vnd.ibexa.api.Foo.Bar+XML' => ['application/vnd.ibexa.api.Foo.Bar+XML', 'xml'];
        yield 'null header' => [null, 'xml'];
    }

    /**
     * @dataProvider getDataForTestExtractFormatFromAcceptHeader
     */
    public function testExtractFormatFromAcceptHeader(?string $acceptHeader, string $expectedFormat): void
    {
        $endpointRequestDefinition = new EndpointRequestDefinition('GET', '/foo', null, $acceptHeader);
        self::assertSame($expectedFormat, $endpointRequestDefinition->extractFormatFromAcceptHeader());
    }

    public function testExtractFormatFromAcceptHeaderThrowsExceptionOnInvalidFormat(): void
    {
        $endpointRequestDefinition = new EndpointRequestDefinition('GET', '/foo', null, 'application/www-urlencoded');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Format can be either xml or json. The given accept header: application/www-urlencoded'
        );
        $endpointRequestDefinition->extractFormatFromAcceptHeader();
    }

    public function testWithAcceptHeader(): void
    {
        $endpointRequestDefinition = new EndpointRequestDefinition('GET', '/foo', null, 'application/xml');

        $clonedEndpointRequestDefinition = $endpointRequestDefinition->withAcceptHeader(null);
        self::assertNotSame($endpointRequestDefinition, $clonedEndpointRequestDefinition);
        self::assertNull($clonedEndpointRequestDefinition->getAcceptHeader());

        $newHeader = 'application/json';
        $clonedEndpointRequestDefinition = $endpointRequestDefinition->withAcceptHeader($newHeader);
        self::assertNotSame($endpointRequestDefinition, $clonedEndpointRequestDefinition);
        self::assertSame($newHeader, $clonedEndpointRequestDefinition->getAcceptHeader());
    }

    public function testWithSnapshotName(): void
    {
        $endpointRequestDefinition = new EndpointRequestDefinition('GET', '/foo', null, 'application/xml');
        self::assertNull($endpointRequestDefinition->getSnapshotName());

        $snapshotName = 'bar/baz';
        $clonedEndpointRequestDefinition = $endpointRequestDefinition->withSnapshotName($snapshotName);
        self::assertNotSame($endpointRequestDefinition, $clonedEndpointRequestDefinition);
        self::assertNull($endpointRequestDefinition->getSnapshotName());
        self::assertSame($snapshotName, $clonedEndpointRequestDefinition->getSnapshotName());
    }

    public function testWithExpectedStatusCode(): void
    {
        $endpointRequestDefinition = new EndpointRequestDefinition('GET', '/foo', null, 'application/xml');
        self::assertNull($endpointRequestDefinition->getExpectedStatusCode());

        $endpointRequestDefinition = $endpointRequestDefinition->withExpectedStatusCode(Response::HTTP_CREATED);
        self::assertSame(Response::HTTP_CREATED, $endpointRequestDefinition->getExpectedStatusCode());

        $endpointRequestDefinition = new EndpointRequestDefinition(
            'GET',
            '/foo',
            null,
            'application/xml',
            [],
            null,
            null,
            null,
            Response::HTTP_NO_CONTENT
        );
        self::assertSame(Response::HTTP_NO_CONTENT, $endpointRequestDefinition->getExpectedStatusCode());
    }
}

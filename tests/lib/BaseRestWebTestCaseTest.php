<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Test\Rest;

use Ibexa\Contracts\Test\Rest\BaseRestWebTestCase;
use Ibexa\Contracts\Test\Rest\Request\Value\EndpointRequestDefinition;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ibexa\Contracts\Test\Rest\BaseRestWebTestCase
 */
final class BaseRestWebTestCaseTest extends TestCase
{
    public function testGetEndpointsDataRequiresEndpointsToTest(): void
    {
        $testCase = new class() extends BaseRestWebTestCase {
            protected function getSchemaFileBasePath(string $resourceType, string $format): string
            {
                return $resourceType;
            }
        };

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageMatches(
            '/@anonymous.*needs to implement .*BaseRestWebTestCase::getEndpointsToTest method/'
        );
        foreach ($testCase::getEndpointsData() as $endpointsDatum) {
            // nothing to do, dummy fail
            self::fail("Expected not to find: $endpointsDatum[0]");
        }
    }

    public function testGetEndpointsDataBuildsProperDataProvider(): void
    {
        $testCase = new class() extends BaseRestWebTestCase {
            public static function getEndpointsToTest(): iterable
            {
                yield new EndpointRequestDefinition('GET', '/foo', null);
            }

            protected function getSchemaFileBasePath(string $resourceType, string $format): string
            {
                return $resourceType;
            }
        };

        $endpointsData = [];
        foreach ($testCase::getEndpointsData() as $dataSetName => $dataSetData) {
            self::assertCount(1, $dataSetData);
            self::assertInstanceOf(EndpointRequestDefinition::class, $dataSetData[0]);
            $endpointsData[$dataSetName] = $dataSetData[0];
        }
        self::assertArrayHasKey('GET /foo accepting xml format without payload', $endpointsData);
        self::assertArrayHasKey('GET /foo accepting json format without payload', $endpointsData);
        self::assertSame('xml', $endpointsData['GET /foo accepting xml format without payload']->getFormat());
        self::assertSame('json', $endpointsData['GET /foo accepting json format without payload']->getFormat());
    }
}

<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Test\Rest\Request\Value;

use Ibexa\Contracts\Test\Rest\Request\Value\EndpointRequestDefinition;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ibexa\Contracts\Test\Rest\Request\Value\EndpointRequestDefinition
 */
final class EndpointRequestDefinitionTest extends TestCase
{
    public function testWithSnapshotName(): void
    {
        $endpointRequestDefinition = new EndpointRequestDefinition('GET', '/foo', null);
        self::assertNull($endpointRequestDefinition->getSnapshotName());

        $snapshotName = 'bar/baz';
        $clonedEndpointRequestDefinition = $endpointRequestDefinition->withSnapshotName($snapshotName);
        self::assertNotSame($endpointRequestDefinition, $clonedEndpointRequestDefinition);
        self::assertNull($endpointRequestDefinition->getSnapshotName());
        self::assertSame($snapshotName, $clonedEndpointRequestDefinition->getSnapshotName());
    }
}

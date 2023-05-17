<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Test\Rest;

use Ibexa\Contracts\Test\Rest\WebTestCase as BaseWebTestCase;

final class TestCaseSample extends BaseWebTestCase
{
    /**
     * @param "json"|"xml"|null $type
     */
    public function testComparison(string $snapshotContent, ?string $type, ?string $file): void
    {
        self::assertStringMatchesSnapshot(
            $snapshotContent,
            $type,
            $file,
        );
    }
}

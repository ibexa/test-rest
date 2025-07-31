<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\Test\Rest\Schema;

use Ibexa\Contracts\Test\Rest\Schema\AbstractFileSchemaProvider;

final class TestSchemaProvider extends AbstractFileSchemaProvider
{
    /**
     * @throws \JsonException
     */
    public function provideSchemas(): iterable
    {
        yield 'ibexa/test-rest/basic_types' => $this->decodeFile(__DIR__ . '/../basic_types.json');
    }
}

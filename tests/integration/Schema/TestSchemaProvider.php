<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\Test\Rest\Schema;

use Ibexa\Contracts\Test\Rest\Schema\SchemaProviderInterface;

final class TestSchemaProvider implements SchemaProviderInterface
{
    public function provideSchemas(): iterable
    {
        $file = __DIR__ . '/../basic_types.json';
        $schema = $this->decode($file);

        yield 'ibexa/test-rest/basic_types' => $schema;
    }

    /**
     * @throws \JsonException
     */
    private function decode(string $file): object
    {
        $basicTypes = file_get_contents($file);
        if ($basicTypes === false) {
            throw new \LogicException('Failed to load basic types schema from file: ' . $file);
        }

        $schema = json_decode($basicTypes, false, 512, JSON_THROW_ON_ERROR);
        if (!is_object($schema)) {
            throw new \LogicException(sprintf(
                'Failed to decode basic types schema from file: %s. Schema is not an object.',
                $file,
            ));
        }

        return $schema;
    }
}

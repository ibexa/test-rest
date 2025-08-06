<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Test\Rest\Schema;

use LogicException;

abstract class AbstractFileSchemaProvider implements SchemaProviderInterface
{
    /**
     * @throws \JsonException
     */
    final protected function decodeFile(string $file): object
    {
        $basicTypes = file_get_contents($file);
        if ($basicTypes === false) {
            throw new LogicException('Failed to load basic types schema from file: ' . $file);
        }

        $schema = json_decode($basicTypes, false, 512, JSON_THROW_ON_ERROR);
        if (!is_object($schema)) {
            throw new LogicException(sprintf(
                'Failed to decode basic types schema from file: %s. Schema is not an object.',
                $file,
            ));
        }

        return $schema;
    }
}

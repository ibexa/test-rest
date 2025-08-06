<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Test\Rest\Schema;

use JsonSchema\SchemaStorage;
use JsonSchema\UriRetrieverInterface;

final class SchemaStorageFactory
{
    /**
     * @param iterable<\Ibexa\Contracts\Test\Rest\Schema\SchemaProviderInterface> $schemas
     */
    public function create(
        UriRetrieverInterface $uriRetriever,
        iterable $schemas
    ): SchemaStorage {
        $storage = new SchemaStorage($uriRetriever);

        foreach ($schemas as $schemaProvider) {
            foreach ($schemaProvider->provideSchemas() as $id => $schema) {
                $storage->addSchema("internal://$id", $schema);
            }
        }

        return $storage;
    }
}

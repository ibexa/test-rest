<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Test\Rest\Schema;

interface SchemaProviderInterface
{
    /**
     * Return schema objects, indexed by their "$id".
     *
     * Will be automatically prefixed with "internal://".
     *
     * @return iterable<non-empty-string, object>
     */
    public function provideSchemas(): iterable;
}

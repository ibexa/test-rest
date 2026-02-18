<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Test\Rest\Schema;

use Ibexa\Contracts\Test\Rest\Schema\SchemaProviderInterface;

final class ErrorSchemaProvider implements SchemaProviderInterface
{
    public function provideSchemas(): iterable
    {
        yield 'ibexa/rest/ErrorMessage' => (object)[
            'type' => 'object',
            'properties' => [
                'ErrorMessage' => [
                    'type' => 'object',
                    'properties' => [
                        '_media-type' => [
                            'type' => 'string',
                        ],
                        'errorCode' => [
                            'type' => 'integer',
                        ],
                        'errorMessage' => [
                            'type' => 'string',
                        ],
                        'errorDescription' => [
                            'type' => 'string',
                        ],
                    ],
                    'required' => [
                        '_media-type',
                        'errorCode',
                        'errorMessage',
                        'errorDescription',
                    ],
                ],
            ],
            'required' => ['ErrorMessage'],
        ];
    }
}

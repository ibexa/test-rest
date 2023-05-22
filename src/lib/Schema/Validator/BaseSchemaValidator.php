<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Test\Rest\Schema\Validator;

use Ibexa\Contracts\Test\Rest\Schema\ValidatorInterface;
use PHPUnit\Framework\Assert;

abstract class BaseSchemaValidator implements ValidatorInterface
{
    private string $schemaDirectory;

    public function __construct(string $schemaDirectory)
    {
        $this->schemaDirectory = $schemaDirectory;
    }

    protected function buildSchemaFilePath(string $schemaName, string $format): string
    {
        $schemaFilePath = $this->schemaDirectory . "/$schemaName.$format";
        if (!file_exists($schemaFilePath)) {
            Assert::fail("Schema file '$schemaFilePath' does not exist");
        }

        return $schemaFilePath;
    }
}

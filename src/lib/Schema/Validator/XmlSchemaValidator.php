<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Test\Rest\Schema\Validator;

final class XmlSchemaValidator extends BaseSchemaValidator
{
    public function validate(string $data, string $schemaName): void
    {
        $xmlDocument = new \DOMDocument();
        $xmlDocument->loadXML($data);
        $xmlDocument->schemaValidate($this->buildSchemaFilePath($schemaName, 'xsd'));
    }
}

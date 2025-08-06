<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Test\Rest\Schema\Validator;

use JsonSchema\Validator;
use PHPUnit\Framework\Assert;

final class JsonSchemaValidator extends BaseSchemaValidator
{
    private Validator $validator;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @throws \JsonException
     */
    public function validate(string $data, string $schemaFilePath): void
    {
        $decodedData = json_decode($data, false, 512, JSON_THROW_ON_ERROR);
        if (str_starts_with($schemaFilePath, 'internal://')) {
            $schemaReference = [
                '$ref' => $schemaFilePath,
            ];
        } else {
            $schemaReference = [
                '$ref' => 'file://' . $this->buildSchemaFilePath($schemaFilePath, 'json'),
            ];
        }

        $this->validator->validate($decodedData, $schemaReference);

        Assert::assertTrue($this->validator->isValid(), $this->convertErrorsToString($this->validator));
    }

    private function convertErrorsToString(Validator $validator): string
    {
        $errorMessage = '';
        foreach ($validator->getErrors() as $error) {
            $errorMessage .= sprintf(
                "property: [%s], constraint: %s, error: %s\n",
                $error['property'],
                $error['constraint'],
                $error['message']
            );
        }

        return $errorMessage;
    }
}

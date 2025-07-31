<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\Test\Rest\Schema\Validator;

use Ibexa\Contracts\Test\Core\IbexaKernelTestCase;
use Ibexa\Test\Rest\Schema\Validator\JsonSchemaValidator;
use JsonSchema\SchemaStorageInterface;
use LogicException;
use PHPUnit\Framework\ExpectationFailedException;

final class JsonSchemaValidatorTest extends IbexaKernelTestCase
{
    private JsonSchemaValidator $validator;

    private SchemaStorageInterface $storage;

    protected function setUp(): void
    {
        parent::setUp();
        $core = self::getIbexaTestCore();
        $this->validator = $core->getServiceByClassName(JsonSchemaValidator::class);
        $this->storage = $core->getServiceByClassName(SchemaStorageInterface::class, 'ibexa.test.rest.json_schema.schema_storage');
    }

    public function testValidate(): void
    {
        $json = '{"data": {"xyz": false}}';

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(<<<MESSAGE
        property: [data], constraint: type, error: Object value found, but a string is required
        property: [data], constraint: type, error: Object value found, but an integer is required
        property: [data], constraint: oneOf, error: Failed to match exactly one schema
        
        Failed asserting that false is true.
        MESSAGE);
        $this->validator->validate($json, __DIR__ . '/../../json_schema');
    }

    public function testValidateWithInternal(): void
    {
        $this->storage->addSchema(
            'internal://ibexa/test-rest/json_schema',
            $this->decodeJsonObject($this->loadFile(__DIR__ . '/../../json_schema.json'))
        );

        $json = '{"data": {"xyz": false}}';
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(<<<MESSAGE
        property: [data], constraint: type, error: Object value found, but a string is required
        property: [data], constraint: type, error: Object value found, but an integer is required
        property: [data], constraint: oneOf, error: Failed to match exactly one schema
        
        Failed asserting that false is true.
        MESSAGE);
        $this->validator->validate($json, 'internal://ibexa/test-rest/json_schema');
    }

    private function decodeJsonObject(string $content): object
    {
        return json_decode($content, false, 512, JSON_THROW_ON_ERROR);
    }

    private function loadFile(string $location): string
    {
        $contents = file_get_contents($location);
        if (empty($contents)) {
            throw new LogicException(sprintf(
                'Unable to load file: %s',
                $location,
            ));
        }

        return $contents;
    }
}

<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\Test\Rest;

use Ibexa\Contracts\Test\Rest\WebTestCase;
use JsonSchema\SchemaStorageInterface;
use JsonSchema\Validator;
use LogicException;

final class WebTestCaseTest extends WebTestCase
{
    private SchemaStorageInterface $storage;

    private Validator $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->storage = self::getJsonSchemaStorage();
        $this->validator = self::getJsonSchemaValidator();
    }

    public function testJsonSchemaUsingFile(): void
    {
        $json = '{"data": {"xyz": false}}';
        $decodedData = $this->decodeJsonObject($json);

        $this->validator->validate($decodedData, [
            '$ref' => 'file://' . __DIR__ . '/json_schema.json',
        ]);

        self::assertFalse($this->validator->isValid());
        $expectedErrors = [
            [
                'property' => 'data',
                'pointer' => '/data',
                'message' => 'Object value found, but a string is required',
                'constraint' => 'type',
                'context' => 1,
            ],
            [
                'property' => 'data',
                'pointer' => '/data',
                'message' => 'Object value found, but an integer is required',
                'constraint' => 'type',
                'context' => 1,
            ],
            [
                'property' => 'data',
                'pointer' => '/data',
                'message' => 'Failed to match exactly one schema',
                'constraint' => 'oneOf',
                'context' => 1,
            ],
        ];
        self::assertEquals($expectedErrors, $this->validator->getErrors());
    }

    public function testJsonSchema(): void
    {
        $this->storage->addSchema(
            'internal://json_schema',
            $this->decodeJsonObject($this->loadFile(__DIR__ . '/json_schema.json'))
        );

        $json = '{"data": {"xyz": false}}';
        $decodedData = $this->decodeJsonObject($json);

        $this->validator->validate($decodedData, [
            '$ref' => 'internal://json_schema',
        ]);

        self::assertFalse($this->validator->isValid());
        $expectedErrors = [
            [
                'property' => 'data',
                'pointer' => '/data',
                'message' => 'Object value found, but a string is required',
                'constraint' => 'type',
                'context' => 1,
            ],
            [
                'property' => 'data',
                'pointer' => '/data',
                'message' => 'Object value found, but an integer is required',
                'constraint' => 'type',
                'context' => 1,
            ],
            [
                'property' => 'data',
                'pointer' => '/data',
                'message' => 'Failed to match exactly one schema',
                'constraint' => 'oneOf',
                'context' => 1,
            ],
        ];
        self::assertEquals($expectedErrors, $this->validator->getErrors());
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

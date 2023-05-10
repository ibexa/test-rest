<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\Test\Rest;

use Ibexa\Contracts\Test\Rest\WebTestCase;

final class WebTestCaseTest extends WebTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testJsonSchema(): void
    {
        $json = <<<JSON
            {
                "data": {
                    "xyz": false
                }
            }
        JSON;

        $storage = self::getJsonSchemaStorage();
        $storage->addSchema('internal://basic_types', $this->decodeJsonObject($this->loadFile(__DIR__ . '/basic_types.json')));
        $storage->addSchema('internal://json_schema', $this->decodeJsonObject($this->loadFile(__DIR__ . '/json_schema.json')));

        $decodedData = $this->decodeJsonObject($json);
        $validator = self::getJsonSchemaValidator();

        $validator->validate($decodedData, [
            '$ref' => 'internal://json_schema',
        ]);

        self::assertFalse($validator->isValid());
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
        self::assertEquals($expectedErrors, $validator->getErrors());
    }

    private function decodeJsonObject(string $content): object
    {
        return json_decode($content, false, 512, JSON_THROW_ON_ERROR);
    }

    private function loadFile(string $location): string
    {
        $contents = file_get_contents($location);
        if (empty($contents)) {
            throw new \LogicException(sprintf(
                'Unable to load file: %s',
                $location,
            ));
        }

        return $contents;
    }
}

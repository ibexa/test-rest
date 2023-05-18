<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Test\Rest;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

final class WebTestCaseTest extends TestCase
{
    /**
     * @dataProvider provideForTestPositiveSnapshotComparison
     *
     * @param "json"|"xml"|null $type
     */
    public function testPositiveSnapshotComparison(
        string $snapshotContent,
        ?string $type,
        ?string $file = null
    ): void {
        $testCase = new TestCaseSample();
        $testCase->testComparison($snapshotContent, $type, $file);

        self::assertCount(1, $testCase);
    }

    /**
     * @return iterable<array{non-empty-string, "json"|"xml"|null, 2?: non-empty-string}>
     */
    public static function provideForTestPositiveSnapshotComparison(): iterable
    {
        yield [
            <<<JSON
                {
                    "text": "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor."
                }
            JSON,
            'json',
        ];

        yield [
            <<<XML
                <text>
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor.
                </text>
            XML,
            'xml',
        ];
    }

    /**
     * @dataProvider provideForTestNegativeSnapshotComparison
     *
     * @param "json"|"xml"|null $type
     */
    public function testNegativeSnapshotComparison(
        string $snapshotContent,
        ?string $type,
        ?string $file = null,
        ?string $expectationMessage = null
    ): void {
        $testCase = new TestCaseSample();

        try {
            $testCase->testComparison($snapshotContent, $type, $file);
            self::fail('This test should fail assertions.');
        } catch (ExpectationFailedException $e) {
            self::assertNotEmpty($expectationMessage);
            self::assertStringContainsString(
                $expectationMessage,
                $e->getMessage(),
            );
        }
    }

    /**
     * @return iterable<array{non-empty-string, "json"|"xml"|null, 2?: non-empty-string|null, 3?: string|null}>
     */
    public function provideForTestNegativeSnapshotComparison(): iterable
    {
        yield [
            '{NotAProperJson}',
            'json',
            null,
            "{NotAProperJson}' is valid JSON (Syntax error, malformed JSON)",
        ];

        yield [
            '<foo />',
            'xml',
            __DIR__ . '/_output/different-xml.xml',
            'Failed asserting that two DOM documents are equal',
        ];

        yield [
            <<<JSON
                {
                    "text": "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor."
                }
            JSON,
            'json',
            __DIR__ . '/_output/different-json.json',
            '"text": "Lorem ipsum dolor sit amet, foobar."',
        ];
    }
}

<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Test\Rest;

use Ibexa\Contracts\Test\Rest\SnapshotReplacer;
use PHPUnit\Framework\TestCase;

final class SnapshotReplacerTest extends TestCase
{
    private SnapshotReplacer $replacer;

    protected function setUp(): void
    {
        $this->replacer = new SnapshotReplacer();
    }

    /**
     * @dataProvider provideForXmlDateReplacements
     * @dataProvider provideForJsonDateReplacements
     */
    public function testXmlDateReplacements(
        string $input,
        string $expectedOutput
    ): void {
        $output = $this->replacer->doReplace($input);
        self::assertSame($input, $output, 'No input should change if left unconfigured');

        $this->replacer->withXmlReplacements();
        $this->replacer->withXmlIgnoreIdChanges();
        $this->replacer->withJsonReplacements();
        $this->replacer->withJsonIgnoreIdChanges();
        $output = $this->replacer->doReplace($input);
        self::assertSame($expectedOutput, $output);

        $this->replacer->clearReplacementsMap();
        $output = $this->replacer->doReplace($input);
        self::assertSame($input, $output);
    }

    /**
     * @return iterable<array{non-empty-string, non-empty-string}>
     */
    public static function provideForJsonDateReplacements(): iterable
    {
        yield 'ProductCreateWebTest.json' => self::prepareSnapshots('ProductCreateWebTest', 'json');
        yield 'ProductGetWebTest.json' => self::prepareSnapshots('ProductGetWebTest', 'json');
    }

    /**
     * @return iterable<array{non-empty-string, non-empty-string}>
     */
    public function provideForXmlDateReplacements(): iterable
    {
        yield 'ProductCreateWebTest.xml' => self::prepareSnapshots('ProductCreateWebTest', 'xml');
        yield 'ProductGetWebTest.xml' => self::prepareSnapshots('ProductGetWebTest', 'xml');
    }

    /**
     * @param non-empty-string $filename
     * @param non-empty-string $type
     *
     * @return array{non-empty-string, non-empty-string}
     */
    private static function prepareSnapshots(string $filename, string $type): array
    {
        return [
            self::loadSnapshot(sprintf(__DIR__ . '/_snapshot/%s.input.%s', $filename, $type)),
            self::loadSnapshot(sprintf(__DIR__ . '/_snapshot/%s.output.%s', $filename, $type)),
        ];
    }

    /**
     * @param non-empty-string $location
     *
     * @return non-empty-string
     */
    private static function loadSnapshot(string $location): string
    {
        $content = file_get_contents($location);
        if ($content === false) {
            throw new \LogicException(sprintf('Unable to load file: %s', $location));
        }

        if (empty($content)) {
            throw new \LogicException(sprintf('Loaded file is empty: %s', $location));
        }

        return $content;
    }
}

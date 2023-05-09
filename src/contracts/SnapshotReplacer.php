<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Test\Rest;

final class SnapshotReplacer
{
    public const JSON_DATE_REPLACEMENTS = [
        '~"creationDate": "\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}"~' => '"creationDate": "__CREATION_DATE__"',
        '~"modificationDate": "\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}"~' => '"modificationDate": "__MODIFICATION_DATE__"',
        '~"lastModificationDate": "\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}"~' => '"lastModificationDate": "__LAST_MODIFICATION_DATE__"',
        '~"publishedDate": "\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}"~' => '"publishedDate": "__PUBLISHED_DATE__"',
        '~"created_at": \d+~' => '"created_at": "__CREATED_AT__"',
        '~"updated_at": \d+~' => '"updated_at": "__UPDATED_AT__"',
    ];

    public const XML_DATE_REPLACEMENTS = [
        '~<creationDate>\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}</creationDate>~' => '<creationDate>__CREATION_DATE__</creationDate>',
        '~<modificationDate>\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}</modificationDate>~' => '<modificationDate>__MODIFICATION_DATE__</modificationDate>',
        '~<lastModificationDate>\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}</lastModificationDate>~' => '<lastModificationDate>__LAST_MODIFICATION_DATE__</lastModificationDate>',
        '~<publishedDate>\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}</publishedDate>~' => '<publishedDate>__PUBLISHED_DATE__</publishedDate>',
        '~<created_at>\d+</created_at>~' => '<created_at>__CREATED_AT__</created_at>',
        '~<updated_at>\d+</updated_at>~' => '<updated_at>__UPDATED_AT__</updated_at>',
    ];

    public const JSON_ID_REPLACEMENTS = [
        '~"id": \d+~' => '"id": "__FIXED_ID__"',
        '~"_remoteId": "([a-f0-9]{32})"~' => '"_remoteId": "__REMOTE_ID__"',
        '~"_id": \d+~' => '"_id": "__FIXED_ID__"',
        '~"_href": "\\\/api\\\/ibexa\\\/v2\\\/([\w\\\/]+?)\\\/(\d+)([\\\/]{0,1}[\w\\\/]*)"~' => '"_href": "\/api\/ibexa\/v2\/$1\/__FIXED_ID__$3"',
    ];
    public const XML_ID_REPLACEMENTS = [
        '~<value key="id">\d+</value>~' => '<value key="id">__FIXED_ID__</value>',
        '~<id>\d+</id>~' => '<id>__FIXED_ID__</id>',
        '~<(\w+)(.*) remoteId="([a-f0-9]{32})"(.*)>~' => '<$1$2 remoteId="__REMOTE_ID__"$4>',
        '~<(\w+)(.*) id="(\d+)"(.*)>~' => '<$1$2 id="__FIXED_ID__"$4>',
        '~<(\w+)(.*) href="\/api\/ibexa\/v2\/([\w\/]+?)\/(\d+)(\/{0,1}[\w\/]*)"(.*)>~' => '<$1$2 href="/api/ibexa/v2/$3/__FIXED_ID__$5"$6>',
    ];

    /** @var array<non-empty-string, non-empty-string> */
    public array $replacementMap = [];

    /**
     * @return $this
     */
    public function clearReplacementsMap(): self
    {
        $this->replacementMap = [];

        return $this;
    }

    public function withJsonReplacements(): void
    {
        $this->withReplacements(self::JSON_DATE_REPLACEMENTS);
    }

    public function withXmlReplacements(): void
    {
        $this->withReplacements(self::XML_DATE_REPLACEMENTS);
    }

    public function withJsonIgnoreIdChanges(): void
    {
        $this->withReplacements(self::JSON_ID_REPLACEMENTS);
    }

    public function withXmlIgnoreIdChanges(): void
    {
        $this->withReplacements(self::XML_ID_REPLACEMENTS);
    }

    /**
     * @phpstan-param iterable<non-empty-string, non-empty-string> $replacementMap
     */
    public function withReplacements(iterable $replacementMap): void
    {
        foreach ($replacementMap as $pattern => $replacement) {
            $this->withReplacement($pattern, $replacement);
        }
    }

    /**
     * @phpstan-param non-empty-string $pattern
     * @phpstan-param non-empty-string $replacement
     */
    public function withReplacement(string $pattern, string $replacement): void
    {
        $this->replacementMap[$pattern] = $replacement;
    }

    /**
     * Replaces dynamic values (like creation time) in $content with constant values.
     */
    public function doReplace(string $content): string
    {
        $patterns = array_keys($this->replacementMap);
        $replacements = array_values($this->replacementMap);
        $result = preg_replace($patterns, $replacements, $content);
        if ($result === null) {
            throw new \LogicException('Failed to perform preg_replace: ' . preg_last_error_msg());
        }

        return $result;
    }
}

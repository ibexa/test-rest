<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Test\Rest\Input\Value;

final class InputPayload implements \Stringable
{
    private string $mediaType;

    private string $format;

    private string $content;

    private ?string $name;

    public function __construct(string $mediaType, string $format, string $content, string $name)
    {
        $this->mediaType = $mediaType;
        $this->format = $format;
        $this->content = $content;
        $this->name = $name;
    }

    public function getMediaType(): string
    {
        return $this->mediaType;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}

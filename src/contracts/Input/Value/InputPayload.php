<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Test\Rest\Input\Value;

final class InputPayload
{
    private string $mediaType;

    private string $format;

    private string $content;

    public function __construct(string $mediaType, string $format, string $content)
    {
        $this->mediaType = $mediaType;
        $this->format = $format;
        $this->content = $content;
    }

    public function getMediaType(): string
    {
        return $this->mediaType;
    }

    public function getMediaTypeWithFormat(): string
    {
        return "$this->mediaType+$this->format";
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}

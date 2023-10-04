<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Test\Rest\Input;

use Ibexa\Contracts\Test\Rest\Input\Value\InputPayload;
use PHPUnit\Framework\Assert;

final class PayloadLoader implements PayloadLoaderInterface
{
    private string $payloadDirectory;

    public function __construct(string $payloadDirectory)
    {
        $this->payloadDirectory = $payloadDirectory;
    }

    public function loadPayload(string $mediaType, string $format, ?string $payloadName = null): InputPayload
    {
        $payloadName = $payloadName ?? $mediaType;
        $filePath = $this->payloadDirectory . "/$payloadName.$format";
        Assert::assertFileIsReadable($filePath);

        $content = file_get_contents($filePath);
        Assert::assertNotFalse($content, "Failed to load file '$filePath' contents");

        return new InputPayload($mediaType, $format, $content, "$payloadName $format");
    }
}

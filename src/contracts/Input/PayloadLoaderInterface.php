<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Test\Rest\Input;

use Ibexa\Contracts\Test\Rest\Input\Value\InputPayload;

interface PayloadLoaderInterface
{
    public function loadPayload(string $mediaType, string $format, ?string $payloadName = null): InputPayload;
}

<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\Test\Rest;

use Ibexa\Bundle\Test\Rest\IbexaTestRestBundle;
use Ibexa\Contracts\Test\Core\IbexaTestKernel;

final class TestKernel extends IbexaTestKernel
{
    public function registerBundles(): iterable
    {
        yield from parent::registerBundles();

        yield new IbexaTestRestBundle();
    }
}

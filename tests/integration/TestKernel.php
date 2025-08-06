<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\Test\Rest;

use Ibexa\Bundle\Test\Rest\IbexaTestRestBundle;
use Ibexa\Contracts\Test\Core\IbexaTestKernel;
use Ibexa\Test\Rest\Schema\Validator\JsonSchemaValidator;
use JsonSchema\SchemaStorageInterface;
use Symfony\Component\Config\Loader\LoaderInterface;

final class TestKernel extends IbexaTestKernel
{
    public function registerBundles(): iterable
    {
        yield from parent::registerBundles();

        yield new IbexaTestRestBundle();
    }

    protected static function getExposedServicesByClass(): iterable
    {
        yield from parent::getExposedServicesByClass();

        yield JsonSchemaValidator::class;
    }

    protected static function getExposedServicesById(): iterable
    {
        yield from parent::getExposedServicesById();

        yield 'ibexa.test.rest.json_schema.schema_storage' => SchemaStorageInterface::class;
    }

    protected function loadServices(LoaderInterface $loader): void
    {
        parent::loadServices($loader);

        $loader->load(__DIR__ . '/Resources/services.yaml');
    }
}

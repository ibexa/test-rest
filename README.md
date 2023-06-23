# Warning

**This package is considered both internal and experimental, and may change without notice.**

# Ibexa REST Test package

This package is part of [Ibexa DXP](https://ibexa.co).

To use this package, [install Ibexa DXP](https://doc.ibexa.co/en/latest/install/).

## Getting started

The purpose of the package is to provide Ibexa REST testing framework, reducing boilerplate code.

## Installation

To start using it, install it together with [ibexa/test-core](https://github.com/ibexa/test-core):

```
composer req --dev ibexa/test-rest:^0.1.x-dev ibexa/test-core:^0.1.x-dev
```

## Prerequisites

It is assumed that you already have created an instance of `\Ibexa\Contracts\Core\Test\IbexaTestKernel` and configured it
via `phpunit.integration.xml.dist` or similar PHPUnit configuration file, together with your `./tests/integration/bootstrap.php`.

## Configuration

### Register Bundles

Your custom `IbexaTestKernel::registerBundles` method needs to yield your bundle instance, and at least:

```php
yield from parent::registerBundles();
yield new \Hautelook\TemplatedUriBundle\HautelookTemplatedUriBundle();
yield new \Ibexa\Bundle\Rest\IbexaRestBundle();
yield new \Ibexa\Bundle\Test\Rest\IbexaTestRestBundle();
```

### Configure Container

Next, create REST routing configuration file, in a similar way project configuration file is prepared via recipes, e.g.:
`./tests/integration/Resources/REST/routing/rest.yaml`:

```yaml
ibexa.acme.rest:
  resource: '@IbexaAcmeBundle/Resources/routing/rest.yaml'
  prefix: '%ibexa.rest.path_prefix%'
```

Then, in your custom `IbexaTestKernel::registerContainerConfiguration` method configure the following:

```php
$loader->load(static function (ContainerBuilder $container): void {
    $container->loadFromExtension('framework', [
        'router' => [
            'resource' => __DIR__ . '/Resources/routing.yaml', // path to the file you've just created
        ],
    ]);
  
    $container->setParameter('form.type_extension.csrf.enabled', false);

    self::addSyntheticService($container, \Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface::class);
});
```

### REST Response Schemas directory

REST Integration Test Framework, for each resource response, validates it against its schema (XSD or JSON). Decide where you want to
keep schema files and create a directory for them, e.g.: `./tests/integration/Resources/REST/Schemas`.

## Write your first test

At this point you should be able to write your first test:

```php
use Ibexa\Contracts\Test\Rest\BaseRestWebTestCase;
use Ibexa\Contracts\Test\Rest\Request\Value\EndpointRequestDefinition;

final class GetAcmeTest extends BaseRestWebTestCase
{
    protected static function getEndpointsToTest(): iterable
    {
        $endpointRequestDefinition = new EndpointRequestDefinition(
            'GET',
            '/api/ibexa/v2/acme',
            'AcmeValue', // expected resource type
            self::generateMediaTypeString('AcmeValue', 'xml'), // accept header
            [], // custom HTTP headers to include in the request
            null, // InputPayload instance for POST/PATCH requests
            null, // custom request name for the data provider
            'acme/acme01' // snapshot name
        );

        yield $endpointRequestDefinition;

        yield $endpointRequestDefinition
            ->withAcceptHeader(self::generateMediaTypeString('AcmeValue', 'json'));
    }

    protected function getSchemaFileBasePath(string $resourceType, string $format): string
    {
        return dirname(__DIR__) . '/Resources/REST/Schemas/' . $resourceType;
    }

    protected static function getSnapshotDirectory(): ?string
    {
        return dirname(__DIR__) . '/Resources/REST/Snapshots';
    }
}
```

### Base class

You need to override `\Ibexa\Contracts\Test\Rest\BaseRestWebTestCase` class.

### Define what to test

The Test case class needs to override `getEndpointsToTest` static method which should yield `EndpointRequestDefinition`
describing what endpoints and how you want to test.

Each `EndpointRequestDefinition` tests one endpoint, expecting one specific response format (XML or JSON).

`EndpointRequestDefinition` takes the following constructor arguments, in that order:

* HTTP method type (`'GET'`, `'POST'`, etc.)
* Endpoint URI, including REST prefix.
* Expected Resource Type the endpoint should return (e.g: `'Content'`, `'Product'`, or `null` for `NoContent`).
* Accept HTTP header, e.g. `application/vnd.ibexa.api.Content+xml` (use
  `self::generateMediaTypeString(resourceType, format)` syntax sugar for that).
* Extra HTTP headers if needed.
* Input Payload (`null` for `GET` requests). See
  [Testing endpoints requiring input payload](#testing-endpoints-requiring-input-payload) for more details.
* Endpoint request name used to generate PHPUnit data set name (`null` to use default name)
* Snapshot name, see [Testing snapshots](#testing-snapshots) for more details.

To test an endpoint in another format, you can use `withAcceptHeader` method which will clone the definition changing
Accept header.

### Response contents validation against schema files

Your Test case class needs to define `getSchemaFileBasePath` method which returns schema file base path (including
file name without an extension), e.g.: `./tests/integration/Resources/REST/Schemas/AcmeValue`, where in `Schemas`
directory, there are two schema files: `AcmeValue.xsd` and `AcmeValue.json`. You can generate these schema files using
IDE and/or some online tools.

Typically, you'd want to create your own abstract base class defining this and make each Test case class extend it
instead, e.g.:

```php
abstract class BaseAcmeRestWebTestCase extends BaseRestWebTestCase
{
    protected function getSchemaFileBasePath(string $resourceType, string $format): string
    {
        return dirname(__DIR__) . '/Resources/REST/Schemas/' . $resourceType;
    }
}
```

File extension is not included because it depends on a validator (e.g. XSD for XML files).

### Testing snapshots

If you have a response snapshot to test against, your Test case class needs  to override `getSnapshotDirectory` method
and define snapshot base file path (without extension) when instantiating `EndpointRequestDefinition`.

REST Test Framework looks for a snapshot file concatenating:

```
<snapshot_directory>/<snapshot_name>.<response_format:json|xml>
```

If you want to generate a snapshot at runtime, set the following environment variable:

```bash
TEST_REST_GENERATE_SNAPSHOTS=1
```

### Testing endpoints requiring input payload

To test mutation endpoints (`POST`, `PATCH`, `PUT`, etc.) you need to create input payload files and load them using
`\Ibexa\Contracts\Test\Rest\Input\PayloadLoader` while creating `EndpointRequestDefinition`.

Example:
```php
    public static function getEndpointsToTest(): iterable
    {
        $payloadLoader = new PayloadLoader(dirname(__DIR__) . '/Resources/REST/InputPayloads');

        yield new EndpointRequestDefinition(
            'POST',
            '/api/ibexa/v2/acme',
            'AcmeValue',
            self::generateMediaTypeString('AcmeValue', 'xml'),
            [],
            $payloadLoader->loadPayload('AcmeValueCreate', 'xml', 'Acme/CustomPayloadFileName')
        );

        yield new EndpointRequestDefinition(
            'POST',
            '/api/ibexa/v2/acme',
            'AcmeValue',
            self::generateMediaTypeString('AcmeValue', 'json'),
            [],
            $payloadLoader->loadPayload('AcmeValueCreate', 'json', 'Acme/CustomPayloadFileName')
        );
    }
```

`PayloadLoader::loadPayload` expects 2 arguments: input Media Type, format. Optionally you can pass custom file
base path (without extension) as the 3rd argument. If not given, the file base path will default to the given Media Type.

## COPYRIGHT

Copyright (C) 1999-2023 Ibexa AS (formerly eZ Systems AS). All rights reserved.

## LICENSE

This source code is available separately under the following licenses:

A - Ibexa Business Use License Agreement (Ibexa BUL),
version 2.4 or later versions (as license terms may be updated from time to time)
Ibexa BUL is granted by having a valid Ibexa DXP (formerly eZ Platform Enterprise) subscription,
as described at: https://www.ibexa.co/product
For the full Ibexa BUL license text, please see:
- LICENSE-bul file placed in the root of this source code, or
- https://www.ibexa.co/software-information/licenses-and-agreements (latest version applies)

AND

B - Ibexa Trial and Test License Agreement (Ibexa TTL),
version 2.2 or later versions (as license terms may be updated from time to time)
Trial can be granted by Ibexa, reach out to Ibexa AS for evaluation access: https://www.ibexa.co/about-ibexa/contact-us
For the full Ibexa TTL license text, please see:
- LICENSE file placed in the root of this source code, or
- https://www.ibexa.co/software-information/licenses-and-agreements (latest version applies)

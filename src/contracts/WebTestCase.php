<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Test\Rest;

use Ibexa\Contracts\Test\Core\IbexaKernelTestTrait;
use Ibexa\Core\MVC\Symfony\Security\UserWrapped;
use JsonSchema\SchemaStorageInterface;
use JsonSchema\Validator;
use LogicException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class WebTestCase extends SymfonyWebTestCase
{
    use IbexaKernelTestTrait;

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();

        $user = $this->getUser();
        $this->client->loginUser($user);
    }

    private function getUser(): UserWrapped
    {
        $userService = self::getUserService();
        $apiUser = $userService->loadUserByLogin('admin');
        $symfonyUser = $this->createMock(UserInterface::class);
        $symfonyUser->method('getRoles')->willReturn(['ROLE_USER']);

        return new UserWrapped($symfonyUser, $apiUser);
    }

    /**
     * Replaces dynamic values (like creation time) in $content with constant values.
     */
    private static function doReplace(string $content): string
    {
        $snapshotReplacer = static::getSnapshotReplacer();

        return $snapshotReplacer->doReplace($content);
    }

    protected static function getSnapshotReplacer(): SnapshotReplacer
    {
        $replacer = new SnapshotReplacer();
        $replacer->withJsonReplacements();
        $replacer->withXmlReplacements();
        $replacer->withJsonIgnoreIdChanges();
        $replacer->withXmlIgnoreIdChanges();

        return $replacer;
    }

    protected static function assertResponseMatchesXmlSnapshot(string $content, ?string $file = null): void
    {
        self::assertStringMatchesSnapshot($content, 'xml', $file);
    }

    protected static function assertResponseMatchesJsonSnapshot(string $content, ?string $file = null): void
    {
        self::assertStringMatchesSnapshot($content, 'json', $file);
    }

    protected static function getJsonSchemaValidator(): Validator
    {
        $validator = self::getContainer()->get('ibexa.test.rest.json_schema.validator');
        if (!$validator instanceof Validator) {
            throw new LogicException(sprintf(
                '%s service is not an instance of %s.',
                'ibexa.test.rest.json_schema.validator',
                Validator::class,
            ));
        }

        return $validator;
    }

    protected static function getJsonSchemaStorage(): SchemaStorageInterface
    {
        $storage = self::getContainer()->get('ibexa.test.rest.json_schema.schema_storage');
        if (!$storage instanceof SchemaStorageInterface) {
            throw new LogicException(sprintf(
                '%s service is not an instance of %s.',
                'ibexa.test.rest.json_schema.schema_storage',
                Validator::class,
            ));
        }

        return $storage;
    }

    /**
     * @phpstan-param "xml"|"json"|null $type
     */
    protected static function assertStringMatchesSnapshot(
        string $content,
        ?string $type = null,
        ?string $file = null
    ): void {
        $content = self::doReplace($content);

        if ($file === null) {
            $class = substr(static::class, strrpos(static::class, '\\') + 1);
            $file = self::getDirectory() . '/_output/' . $class . '.' . ($type ?? 'log');
        }

        if (getenv('TEST_REST_GENERATE_SNAPSHOTS')) {
            self::createDirectoryIfNotExists($file);

            if (!file_exists($file)) {
                file_put_contents($file, rtrim($content, "\n") . "\n");
            }
        }

        if ($type === 'xml') {
            self::assertXmlStringEqualsXmlFile($file, $content);
        } elseif ($type === 'json') {
            self::assertJsonStringEqualsJsonFile($file, $content);
        } else {
            self::assertStringEqualsFile($file, $content);
        }
    }

    private static function createDirectoryIfNotExists(string $file): void
    {
        $separatorPos = strrpos($file, \DIRECTORY_SEPARATOR);
        if ($separatorPos !== false) {
            $directory = substr($file, 0, $separatorPos);
            if (!file_exists($directory)) {
                mkdir($directory);
            }
        }
    }

    protected static function getDirectory(): string
    {
        $classInfo = new \ReflectionClass(static::class);
        $classFilename = $classInfo->getFileName();
        if (false === $classFilename) {
            throw new LogicException(
                sprintf(
                    'Failed acquiring directory location for %s',
                    static::class,
                ),
            );
        }

        return dirname($classFilename);
    }
}

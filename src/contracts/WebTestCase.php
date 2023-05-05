<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Test\Rest;

use Ibexa\Contracts\Test\Core\IbexaKernelTestTrait;
use Ibexa\Core\MVC\Symfony\Security\UserWrapped;
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
     * Returns a regexp map, where keys are regexp patterns and values are replacements.
     *
     * @return array<non-empty-string, non-empty-string>
     */
    protected static function getReplacementMap(): array
    {
        return [
            '~"_remoteId": "([a-f0-9]{32})"~' => '"_remoteId": "__REMOTE_ID__"',
            '~"creationDate": "\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}"~' => '"creationDate": "__CREATION_DATE__"',
            '~"modificationDate": "\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}"~' => '"modificationDate": "__MODIFICATION_DATE__"',
            '~"lastModificationDate": "\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}"~' => '"lastModificationDate": "__LAST_MODIFICATION_DATE__"',
            '~"publishedDate": "\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}"~' => '"publishedDate": "__PUBLISHED_DATE__"',
            '~"created_at": \d+~' => '"created_at": "__CREATED_AT__"',
            '~"updated_at": \d+~' => '"updated_at": "__UPDATED_AT__"',
            '~<(\w+)(.*)remoteId="([a-f0-9]{32})"(.*)>~' => '<$1$2remoteId="__REMOTE_ID__"$4>',
            '~<creationDate>\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}</creationDate>~' => '<creationDate>__CREATION_DATE__</creationDate>',
            '~<modificationDate>\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}</modificationDate>~' => '<modificationDate>__MODIFICATION_DATE__</modificationDate>',
            '~<lastModificationDate>\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}</lastModificationDate>~' => '<lastModificationDate>__LAST_MODIFICATION_DATE__</lastModificationDate>',
            '~<publishedDate>\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}</publishedDate>~' => '<publishedDate>__PUBLISHED_DATE__</publishedDate>',
            '~<created_at>\d+</created_at>~' => '<created_at>__CREATED_AT__</created_at>',
            '~<updated_at>\d+</updated_at>~' => '<updated_at>__UPDATED_AT__</updated_at>',
        ];
    }

    /**
     * Replaces dynamic values (like creation time) in $content with constant values.
     */
    private static function doReplace(string $content): string
    {
        $replacementMap = static::getReplacementMap();
        $patterns = array_keys($replacementMap);
        $replacements = array_values($replacementMap);
        $content = preg_replace($patterns, $replacements, $content);
        self::assertNotNull($content);

        return $content;
    }

    protected static function assertResponseMatchesXmlSnapshot(string $content, ?string $file = null): void
    {
        self::assertStringMatchesSnapshot($content, 'xml', $file);
    }

    protected static function assertResponseMatchesJsonSnapshot(string $content, ?string $file = null): void
    {
        self::assertStringMatchesSnapshot($content, 'json', $file);
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

        self::createDirectoryIfNotExists($file);

        if (!file_exists($file)) {
            file_put_contents($file, rtrim($content, "\n") . "\n");
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
            throw new \LogicException(
                sprintf(
                    'Failed acquiring directory location for %s',
                    static::class,
                ),
            );
        }

        return dirname($classFilename);
    }
}

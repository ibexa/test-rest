<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Test\Rest\Schema\Validator;

use Ibexa\Contracts\Test\Rest\Schema\ValidatorInterface;

final class SchemaValidatorRegistry
{
    /** @var array<string, \Ibexa\Contracts\Test\Rest\Schema\ValidatorInterface> */
    private array $validators;

    /**
     * @param iterable<string, \Ibexa\Contracts\Test\Rest\Schema\ValidatorInterface> $validators
     */
    public function __construct(iterable $validators)
    {
        foreach ($validators as $format => $validator) {
            $this->validators[$format] = $validator;
        }
    }

    public function getValidator(string $format): ValidatorInterface
    {
        if (!isset($this->validators[$format])) {
            throw new \RuntimeException("Unknown '$format' schema validator format");
        }

        return $this->validators[$format];
    }
}

<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixture;

/**
 * Represents a Fixture property. For example:
 *
 * user0:
 *  username (unique): '<username()>'
 *
 * For the "username" property, the corresponding PropertyDefinition will have the values:
 *  #name: 'username'
 *  #value: '<username()>'
 *  #requiresUnique: true
 */
final class PropertyDefinition
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $value;

    /**
     * @var bool
     */
    private $requiresUnique;

    public function __construct(string $name, string $value, bool $requiresUnique = false)
    {
        $this->name = $name;
        $this->value = $value;
        $this->requiresUnique = $requiresUnique;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function requiresUnique(): bool
    {
        return $this->requiresUnique;
    }
}

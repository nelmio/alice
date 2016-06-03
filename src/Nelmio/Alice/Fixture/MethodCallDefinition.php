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

final class MethodCallDefinition
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $arguments;

    /**
     * @var bool
     */
    private $requiresUnique;

    public function __construct(string $caller, array $arguments, bool $requiresUnique = false)
    {
        $this->name = $caller;
        $this->arguments = $arguments;
        $this->requiresUnique = $requiresUnique;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * returns true if this property requires unique values
     *
     * @return boolean
     **/
    public function requiresUnique()
    {
        return $this->requiresUnique;
    }
}

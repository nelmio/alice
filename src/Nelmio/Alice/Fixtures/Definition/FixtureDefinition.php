<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Definition;

use Nelmio\Alice\Fixtures\Flag\FlagBag;

final class FixtureDefinition
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $flags;

    /**
     * @var array
     */
    private $specs;

    public function __construct(string $className, string $name, array $specs, FlagBag $flags = null)
    {
        $this->className = $className;
        $this->name = $name;
        $this->specs = $specs;
        $this->flags = $flags;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getFlags(): string
    {
        return $this->flags;
    }

    public function getSpecs(): array
    {
        return $this->specs;
    }

    public function isATemplate(): bool
    {
        return $this->flags->isATemplate();
    }

    public function getExtends(): bool
    {
        return $this->flags->isATemplate();
    }
}

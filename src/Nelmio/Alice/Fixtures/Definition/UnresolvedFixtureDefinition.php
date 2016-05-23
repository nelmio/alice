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

class UnresolvedFixtureDefinition
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

    public function __construct(string $className, string $name, array $specs, FlagBag $flags)
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

    public function getName():string
    {
        return $this->name;
    }

    public function getSpecs(): array
    {
        return $this->specs;
    }

    public function isTemplate(): bool
    {
        //TODO
    }

    public function extendTemplates(): bool
    {
        //TODO
    }

    public function getExtendedTemplates(): array
    {
        //TODO
    }
}

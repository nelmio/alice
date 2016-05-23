<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures;

class NewFixture
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $specs;

    public function __construct(string $className, string $name, array $specs)
    {
        $this->className = $className;
        $this->name = $name;
        $this->specs = $specs;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSpecs(): array
    {
        return $this->specs;
    }
}

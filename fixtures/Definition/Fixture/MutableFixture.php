<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nelmio\Alice\Definition\Fixture;

use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\NotCallableTrait;

class MutableFixture implements FixtureInterface
{
    use NotCallableTrait;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $className;

    /**
     * @var SpecificationBag
     */
    private $specs;

    public function __construct(string $id, string $className, SpecificationBag $specs)
    {
        $this->id = $id;
        $this->className = $className;
        $this->specs = $specs;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getSpecs(): SpecificationBag
    {
        return $this->specs;
    }

    public function setSpecs(SpecificationBag $specs): void
    {
        $this->specs = $specs;
    }

    public function getValueForCurrent(): void
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function withSpecs(SpecificationBag $specs): void
    {
        $this->__call(__METHOD__, func_get_args());
    }
}

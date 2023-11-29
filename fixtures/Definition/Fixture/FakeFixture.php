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

class FakeFixture implements FixtureInterface
{
    use NotCallableTrait;

    public function getId(): string
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getClassName(): string
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getSpecs(): SpecificationBag
    {
        $this->__call(__METHOD__, func_get_args());
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

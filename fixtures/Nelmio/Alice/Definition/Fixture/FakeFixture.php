<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition\Fixture;

use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\NotCallableTrait;

class FakeFixture implements FixtureInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        $this->__call();
    }

    /**
     * @inheritdoc
     */
    public function getClassName(): string
    {
        $this->__call();
    }

    /**
     * @inheritdoc
     */
    public function getSpecs(): SpecificationBag
    {
        $this->__call();
    }

    /**
     * @inheritdoc
     */
    public function withSpecs(SpecificationBag $specs)
    {
        $this->__call();
    }
}

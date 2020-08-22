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

namespace Nelmio\Alice;

use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\Throwable\Exception\NoValueForCurrentException;

/**
 * A fixture is a value object representing an object to be built.
 */
interface FixtureInterface extends FixtureIdInterface
{
    /**
     * @return string FQCN. May contain flags depending of the implementation.
     */
    public function getClassName(): string;

    public function getSpecs(): SpecificationBag;

    /**
     * @throws NoValueForCurrentException
     *
     * @return string|int
     */
    public function getValueForCurrent();

    /**
     * Creates a new instance with the given specs.
     *
     *
     * @return static
     */
    public function withSpecs(SpecificationBag $specs);
}

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

namespace Nelmio\Alice\Definition\Flag;

use Nelmio\Alice\Definition\FlagInterface;
use Nelmio\Alice\Definition\ServiceReference\FixtureReference;

final class ExtendFlag implements FlagInterface
{
    /**
     * @var FixtureReference
     */
    private $extendedFixture;

    /**
     * @var string
     */
    private $stringValue;

    /**
     * @param FixtureReference $extendedFixture Reference of the extended fixture.
     *
     * @example
     *  For (extends user0), $extendedFixture is 'user0'
     */
    public function __construct(FixtureReference $extendedFixture)
    {
        $this->extendedFixture = $extendedFixture;
        $this->stringValue = 'extends '.$extendedFixture->getId();
    }

    public function getExtendedFixture(): FixtureReference
    {
        return clone $this->extendedFixture;
    }

    /**
     * @return string Flag string representation. Is used as an identifier to easily access to a specific flag.
     */
    public function __toString(): string
    {
        return $this->stringValue;
    }
}

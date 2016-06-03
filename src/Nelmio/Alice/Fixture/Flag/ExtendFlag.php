<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixture\Flag;

use Nelmio\Alice\Fixture\FlagInterface;

final class ExtendFlag implements FlagInterface
{
    /**
     * @var string
     */
    private $extendedFixture;

    /**
     * @param string $extendedFixture Reference of the extended fixture.
     *                                
     * @example
     *  For (extends user0), $extendedFixture is 'user0'
     */
    public function __construct(string $extendedFixture)
    {
        $this->extendedFixture = $extendedFixture;
    }

    public function getExtendedFixture(): string
    {
        return $this->extendedFixture;
    }

    /**
     * @return string Flag string representation. Is used as an identifier to easily access to a specific flag.
     */
    public function __toString(): string
    {
        return sprintf('extends %s', $this->extendedFixture);
    }
}

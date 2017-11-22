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

namespace Nelmio\Alice\Faker\Provider;

use Nelmio\Alice\FixtureInterface;

final class AliceProvider
{
    /**
     * Returns whatever is passed to it. This allows you among other things to use a PHP expression while still
     * benefiting from variable replacement.
     *
     * @param mixed $expression
     *
     * @return mixed
     */
    public static function identity($expression)
    {
        return $expression;
    }

    /**
     * @return string
     */
    public static function current(FixtureInterface $fixture)
    {
        return $fixture->getValueForCurrent();
    }
}

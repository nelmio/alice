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

namespace Nelmio\Alice\Throwable\Exception\Generator\Hydrator;

use Nelmio\Alice\Throwable\HydrationThrowable;

/**
 * Unlike most InvalidArgumentException thrown, this one is not a LogicException as in the context of hydration, this
 * exception can be thrown because the wrong accessor is used and hence should be catchable to try another accessor
 * for example.
 */
class InvalidArgumentException extends \RuntimeException implements HydrationThrowable
{
}

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

namespace Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer;

use Nelmio\Alice\Throwable\DenormalizationThrowable;
use UnexpectedValueException;

/**
 * In the denormalization process to build fixture objects, a scope, i.e. a fixture reference, is always given. This
 * scopes is necessary for the unique values to which the uniqueness is bound.
 */
class InvalidScopeException extends UnexpectedValueException implements DenormalizationThrowable
{
}

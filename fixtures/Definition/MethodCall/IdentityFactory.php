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

namespace Nelmio\Alice\Definition\MethodCall;

use Nelmio\Alice\Definition\Value\EvaluatedValue;
use Nelmio\Alice\Definition\Value\FunctionCallValue;

class IdentityFactory
{
    public static function create(string $expression): FunctionCallValue
    {
        return new FunctionCallValue(
            'identity',
            [new EvaluatedValue($expression)]
        );
    }
}

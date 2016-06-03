<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice;

interface ExpressionLanguageInterface
{
    /**
     * @param string       $expression
     * @param ParameterBag $parameters
     * @param ObjectBag    $objects
     *
     * @return mixed
     */
    public function evaluate(string $expression, ParameterBag $parameters, ObjectBag $objects);
}

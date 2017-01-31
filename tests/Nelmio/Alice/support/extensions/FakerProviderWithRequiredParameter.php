<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\support\extensions;

/**
 * Faker provider with a method that takes 1 mandatory parameter.
 */
class FakerProviderWithRequiredParameter
{
    public function passValue($value)
    {
        return $value;
    }
}

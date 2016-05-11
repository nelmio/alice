<?php

/*
 * This file is part of the Alice package.
 *  
 *  (c) Nelmio <hello@nelm.io>
 *  
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\Instantiator;

use Nelmio\Alice\Throwable\InstantiatorThrowable;

class RuntimeException extends \RuntimeException implements InstantiatorThrowable
{
}

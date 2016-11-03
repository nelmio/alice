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

namespace Nelmio\Alice\Entity\Instantiator;

class AmbiguousDummyWithConstructorWithArrayParameter
{
    /**
     * @var bool
     */
    private $constructor = false;

    /**
     * @var array
     */
    private $value;

    public function __construct(array $value)
    {
        $this->value = $value;
        $this->constructor = true;
    }

    // Static constructor which has the same name as the constructor parameter
    public static function value(array $value): self
    {
        $instance = new static($value);
        $instance->constructor = false;

        return $instance;
    }
}

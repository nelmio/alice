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

class DummyWithConstructor
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $age;

    /**
     * @var bool
     */
    private $isAdult;

    /**
     * @var bool
     */
    private $constructor = false;

    public function __construct(string $name, int $age = 10, bool $isAdult)
    {
        $this->name = $name;
        $this->age = $age;
        $this->isAdult = $isAdult;
        $this->constructor = true;
    }

    public static function create(string $name, int $age = 10, bool $isAdult): self
    {
        $instance = new static($name, $age, $isAdult);
        $instance->constructor = false;

        return $instance;
    }
}

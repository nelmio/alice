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

namespace Nelmio\Alice\Definition;

/**
 * Represents a function call that will be done on the object described by the fixtures once it has been instantiated
 * and hydrated.
 */
interface MethodCallInterface
{
    /**
     * @param ValueInterface[]|array|null $arguments
     *
     * @return static
     */
    public function withArguments(array $arguments = null);

    /**
     * @return ServiceReferenceInterface|null No caller means that the caller is the instance of the object itself.
     */
    public function getCaller();
    
    /**
     * @return string Method name
     */
    public function getMethod(): string;

    /**
     * @return ValueInterface[]|array|null
     */
    public function getArguments();
    
    public function __toString(): string;
}

<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixture;

/**
 * Represents a Fixture method call
 *
 * @example
 *  user0:
 *    __calls:
 *      - [ setLocation, [0.49, 50] ]
 *
 *  In the above fixture, there is a MethodCall with the following properties:
 *    #method: 'setLocation'
 *    #arguments: [
 *      0.49,
 *      50,
 *    ]
 */
final class MethodCall
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var PropertyDefinition[]|null
     */
    private $arguments;

    /**
     * @param string                    $method
     * @param PropertyDefinition[]|null $arguments
     */
    public function __construct(string $method, array $arguments = null)
    {
        $this->method = $method;
        $this->arguments = $arguments;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return PropertyDefinition[]|null
     */
    public function getArguments()
    {
        return $this->arguments;
    }
}

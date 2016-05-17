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

final class Specifications
{
    /**
     * @var MethodCall[]|null
     */
    private $calls;

    /**
     * @var MethodCall
     */
    private $constructor;

    /**
     * @var PropertyDefinition[]
     */
    private $properties;

    /**
     * @var bool
     */
    private $useConstructor = true;

    private function __construct()
    {
    }

    public static function create(MethodCall $constructor = null, array $properties, array $calls = []): self
    {
        $instance = new self();

        $instance->constructor = $constructor;
        $instance->properties = $properties;
        $instance->calls = $calls;

        return $instance;
    }

    public static function createWithNoConstructor(array $properties, array $calls = []): self
    {
        $instance = new self();

        $instance->useConstructor = false;
        $instance->properties = $properties;
        $instance->calls = $calls;

        return $instance;
    }

    public function extend(self $extendedSpecs): self
    {
        $clone = clone $this;

        if (null === $this->constructor && $this->useConstructor) {
            $clone->constructor = $extendedSpecs->getConstructor();
            $clone->useConstructor = $extendedSpecs->shouldUseConstructor();
        }

        $clone->properties = array_merge($extendedSpecs->getProperties(), $clone->properties);
        $clone->calls = array_merge($extendedSpecs->getCalls(), $clone->calls);

        return $clone;
    }

    public function shouldUseConstructor(): bool
    {
        return $this->useConstructor;
    }

    /**
     * @throws \BadMethodCallException
     *
     * @return MethodCall|null
     */
    public function getConstructor()
    {
        if (false === $this->useConstructor) {
            return $this->constructor;
        }

        throw new \BadMethodCallException(
            sprintf(
                'The method "%s" has been called but "useConstructor" is set to false',
                __METHOD__
            )
        );
    }

    /**
     * @return PropertyDefinition[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return MethodCall[]
     */
    public function getCalls(): array
    {
        return $this->calls;
    }
}

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

namespace Nelmio\Alice\Generator\Instantiator\Chainable;

use Nelmio\Alice\Definition\MethodCall\NoMethodCall;
use Nelmio\Alice\Definition\ServiceReference\StaticReference;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\NamedArgumentsResolver;
use Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationExceptionFactory;

final class StaticFactoryInstantiator extends AbstractChainableInstantiator
{
    /**
     * @var NamedArgumentsResolver|null
     */
    private $namedArgumentsResolver;

    // TODO: make $namedArgumentsResolver non-nullable in 4.0. It is currently nullable only for BC purposes
    public function __construct(NamedArgumentsResolver $namedArgumentsResolver = null)
    {
        $this->namedArgumentsResolver = $namedArgumentsResolver;
    }
    
    public function canInstantiate(FixtureInterface $fixture): bool
    {
        $constructor = $fixture->getSpecs()->getConstructor();

        return null !== $constructor && false === $constructor instanceof NoMethodCall && $constructor->getCaller() instanceof StaticReference;
    }
    
    protected function createInstance(FixtureInterface $fixture)
    {
        $constructor = $fixture->getSpecs()->getConstructor();
        [$class, $factory, $method, $arguments] = [
            $fixture->getClassName(),
            $constructor->getCaller()->getId(),
            $constructor->getMethod(),
            $constructor->getArguments()
        ];

        if (null === $arguments) {
            $arguments = [];
        }

        if (null !== $this->namedArgumentsResolver) {
            $arguments = $this->namedArgumentsResolver->resolveArguments($arguments, $factory, $method);
        }

        $instance = $factory::$method(...array_values($arguments));
        if (false === $instance instanceof $class) {
            throw InstantiationExceptionFactory::createForInvalidInstanceType($fixture, $instance);
        }

        return $instance;
    }
}

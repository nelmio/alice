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
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\NamedArgumentsResolver;

final class NoCallerMethodCallInstantiator extends AbstractChainableInstantiator
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

        return null !== $constructor && false === $constructor instanceof NoMethodCall && null === $constructor->getCaller();
    }
    
    protected function createInstance(FixtureInterface $fixture)
    {
        [$class, $arguments] = [
            $fixture->getClassName(),
            $fixture->getSpecs()->getConstructor()->getArguments()
        ];

        if (null !== $this->namedArgumentsResolver) {
            $arguments = $this->namedArgumentsResolver->resolveArguments($arguments, $class, '__construct');
        }

        return new $class(...array_values($arguments));
    }
}

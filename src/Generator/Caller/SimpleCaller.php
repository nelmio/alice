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

namespace Nelmio\Alice\Generator\Caller;

use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\CallerInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\NamedArgumentsResolver;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ObjectInterface;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundExceptionFactory;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueDuringGenerationExceptionFactory;
use Nelmio\Alice\Throwable\ResolutionThrowable;

final class SimpleCaller implements CallerInterface, ValueResolverAwareInterface
{
    use IsAServiceTrait;

    /**
     * @var CallProcessorInterface
     */
    private $callProcessor;

    /**
     * @var ValueResolverInterface
     */
    private $resolver;

    /**
     * @var NamedArgumentsResolver|null
     */
    private $namedArgumentsResolver;

    // TODO: make $namedArgumentsResolver non-nullable in 4.0. It is currently nullable only for BC purposes
    public function __construct(
        CallProcessorInterface $callProcessor,
        ValueResolverInterface $resolver = null,
        NamedArgumentsResolver $namedArgumentsResolver = null
    ) {
        $this->callProcessor = $callProcessor;
        $this->resolver = $resolver;
        $this->namedArgumentsResolver = $namedArgumentsResolver;
    }
    
    public function withValueResolver(ValueResolverInterface $resolver): self
    {
        return new self($this->callProcessor, $resolver, $this->namedArgumentsResolver);
    }
    
    public function doCallsOn(
        ObjectInterface $object,
        ResolvedFixtureSet $fixtureSet,
        GenerationContext $context
    ): ResolvedFixtureSet {
        if (null === $this->resolver) {
            throw ResolverNotFoundExceptionFactory::createUnexpectedCall(__METHOD__);
        }

        $fixture = $fixtureSet->getFixtures()->get($object->getId());
        $calls = $fixture->getSpecs()->getMethodCalls();

        foreach ($calls as $methodCall) {
            $scope = [
                '_instances' => $fixtureSet->getObjects()->toArray(),
            ];

            [$methodCall, $fixtureSet] = $this->processArguments(
                $methodCall,
                $fixture,
                $fixtureSet,
                $scope,
                $context
            );

            $fixtureSet = $this->callProcessor->process($object, $fixtureSet, $context, $methodCall);
        }

        return $fixtureSet;
    }

    private function processArguments(
        MethodCallInterface $methodCall,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        $scope,
        GenerationContext $context
    ): array {
        $arguments = $methodCall->getArguments();

        if (null === $arguments) {
            $arguments = [];
        }

        foreach ($arguments as $k => &$value) {
            if ($value instanceof ValueInterface) {
                try {
                    $result = $this->resolver->resolve($value, $fixture, $fixtureSet, $scope, $context);
                } catch (ResolutionThrowable $throwable) {
                    throw UnresolvableValueDuringGenerationExceptionFactory::createFromResolutionThrowable($throwable);
                }

                [$value, $fixtureSet] = [$result->getValue(), $result->getSet()];
            }
        }
        unset($value);

        if (null !== $this->namedArgumentsResolver) {
            $arguments = $this->namedArgumentsResolver->resolveArguments($arguments, $fixture->getClassName(), $methodCall->getMethod());
        }

        return [$methodCall->withArguments($arguments), $fixtureSet];
    }
}

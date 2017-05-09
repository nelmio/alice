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

namespace Nelmio\Alice\Generator\Caller\Chainable;

use Nelmio\Alice\Definition\MethodCall\MethodCallWithReference;
use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\Definition\ServiceReference\StaticReference;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\Caller\ChainableCallProcessorInterface;
use Nelmio\Alice\Generator\CallerInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ObjectInterface;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundExceptionFactory;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueDuringGenerationExceptionFactory;
use Nelmio\Alice\Throwable\InstantiationThrowable;
use Nelmio\Alice\Throwable\ResolutionThrowable;

final class MethodCallWithReferenceProcessor implements ChainableCallProcessorInterface
{
    use IsAServiceTrait;

    /**
     * @inheritdoc
     */
    public function canProcess(MethodCallInterface $methodCall): bool
    {
        return (
            $methodCall instanceof MethodCallWithReference
            && null !== $methodCall->getCaller()
            && $methodCall->getCaller() instanceof StaticReference
        );
    }

    /**
     * {@inheritdoc}
     *
     * @param MethodCallWithReference $methodCall
     */
    public function process(
        ObjectInterface $object,
        ResolvedFixtureSet $fixtureSet,
        GenerationContext $context,
        MethodCallInterface $methodCall
    ): ResolvedFixtureSet
    {
        /** @var StaticReference $reference */
        $reference = $methodCall->getCaller();

        if (false === ($reference instanceof StaticReference)) {
            //TODO: throw error
        }

        $reference->getId()::{$methodCall->getMethod()}(...$methodCall->getArguments());

        return $fixtureSet->withObjects(
            $fixtureSet->getObjects()->with($object)
        );
    }
}

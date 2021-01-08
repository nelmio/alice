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

use LogicException;
use Nelmio\Alice\Definition\MethodCall\MethodCallWithReference;
use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\Definition\ServiceReference\StaticReference;
use Nelmio\Alice\Generator\Caller\ChainableCallProcessorInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ObjectInterface;

final class MethodCallWithReferenceProcessor implements ChainableCallProcessorInterface
{
    use IsAServiceTrait;
    
    public function canProcess(MethodCallInterface $methodCall): bool
    {
        return (
            $methodCall instanceof MethodCallWithReference
            && null !== $methodCall->getCaller()
            && $methodCall->getCaller() instanceof StaticReference
        );
    }

    /**
     * @param MethodCallWithReference $methodCall
     */
    public function process(
        ObjectInterface $object,
        ResolvedFixtureSet $fixtureSet,
        GenerationContext $context,
        MethodCallInterface $methodCall
    ): ResolvedFixtureSet {
        /** @var StaticReference $reference */
        $reference = $methodCall->getCaller();

        if (false === ($reference instanceof StaticReference)) {
            throw new LogicException('TODO');
        }

        $result = $reference->getId()::{$methodCall->getMethod()}(...$methodCall->getArguments());

        if ($context->needsCallResult()) {
            $object = $object->withInstance($result);
        }

        return $fixtureSet->withObjects(
            $fixtureSet->getObjects()->with($object)
        );
    }
}

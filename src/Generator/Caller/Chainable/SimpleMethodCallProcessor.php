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

use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\Generator\Caller\ChainableCallProcessorInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ObjectInterface;

final class SimpleMethodCallProcessor implements ChainableCallProcessorInterface
{
    use IsAServiceTrait;

    /**
     * @inheritdoc
     */
    public function canProcess(MethodCallInterface $methodCall): bool
    {
        return $methodCall instanceof SimpleMethodCall;
    }

    /**
     * @inheritdoc
     */
    public function process(
        ObjectInterface $object,
        ResolvedFixtureSet $fixtureSet,
        GenerationContext $context,
        MethodCallInterface $methodCall
    ): ResolvedFixtureSet {
        $result = $object->getInstance()->{$methodCall->getMethod()}(...$methodCall->getArguments());

        if ($context->needsCallResult()) {
            $object = $object->withInstance($result);
        }

        return $fixtureSet->withObjects(
            $fixtureSet->getObjects()->with($object)
        );
    }
}

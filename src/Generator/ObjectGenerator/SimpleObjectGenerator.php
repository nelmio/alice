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

namespace Nelmio\Alice\Generator\ObjectGenerator;

use Error;
use LogicException;
use Nelmio\Alice\Definition\Object\CompleteObject;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\CallerInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\HydratorInterface;
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Generator\ObjectGeneratorAwareInterface;
use Nelmio\Alice\Generator\ObjectGeneratorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\Throwable\Exception\Generator\DebugUnexpectedValueException;
use RuntimeException;
use Throwable;

final class SimpleObjectGenerator implements ObjectGeneratorInterface
{
    use IsAServiceTrait;
    
    /**
     * @var InstantiatorInterface
     */
    private $instantiator;
    
    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @var CallerInterface
     */
    private $caller;

    public function __construct(
        ValueResolverInterface $valueResolver,
        InstantiatorInterface $instantiator,
        HydratorInterface $hydrator,
        CallerInterface $caller
    ) {
        if ($valueResolver instanceof ObjectGeneratorAwareInterface) {
            $valueResolver = $valueResolver->withObjectGenerator($this);
        }

        if ($instantiator instanceof ValueResolverAwareInterface) {
            $instantiator = $instantiator->withValueResolver($valueResolver);
        }

        if ($hydrator instanceof ValueResolverAwareInterface) {
            $hydrator = $hydrator->withValueResolver($valueResolver);
        }

        if ($caller instanceof ValueResolverAwareInterface) {
            $caller = $caller->withValueResolver($valueResolver);
        }

        $this->instantiator = $instantiator;
        $this->hydrator = $hydrator;
        $this->caller = $caller;
    }
    
    public function generate(
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        GenerationContext $context
    ): ObjectBag {
        // TODO: move this outside of this in a dedicated class e.g. TolerantObjectGenerator which could decorate the
        // root ObjectGeneratorInterface instance used
        try {
            return $this->generateObject(...func_get_args());
        } catch (DebugUnexpectedValueException $exception) {
            throw $exception;
        } catch (RuntimeException $throwable) {
            $throwableClass = DebugUnexpectedValueException::class;
        } catch (LogicException $throwable) {
            $throwableClass = LogicException::class;
        } catch (Throwable $throwable) {
            $throwableClass = Error::class;
        }

        $arguments = [
            sprintf(
                'An error occurred while generating the fixture "%s" (%s): %s',
                $fixture->getId(),
                $fixture->getClassName(),
                $throwable->getMessage()
            ),
            $throwable->getCode(),
            $throwable
        ];

        throw new $throwableClass(...$arguments);
    }

    private function generateObject(
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        GenerationContext $context
    ): ObjectBag {
        if ($context->isFirstPass()) {
            $fixtureSet = $this->instantiator->instantiate($fixture, $fixtureSet, $context);

            if (false === $context->needsCompleteGeneration()) {
                $fixtureSet = $this->tryToMarkObjectAsComplete($fixture, $fixtureSet, $context);

                return $fixtureSet->getObjects();
            }
        }

        $fixtureSet = $this->completeObject($fixture, $fixtureSet, $context);

        return $fixtureSet->getObjects();
    }

    private function completeObject(
        FixtureInterface $fixture,
        ResolvedFixtureSet $set,
        GenerationContext $context
    ): ResolvedFixtureSet {
        $instantiatedObject = $set->getObjects()->get($fixture);

        $set = $this->hydrator->hydrate($instantiatedObject, $set, $context);
        $hydratedObject = $set->getObjects()->get($fixture);

        $set = $this->caller->doCallsOn($hydratedObject, $set, $context);

        return $this->tryToMarkObjectAsComplete($fixture, $set, $context);
    }

    // TODO: This could be moved back to CompleteObjectGenerator in 4.0. Indeed this is done here meanwhile because
    // the generator injected to the value resolver is this one instead of the parent CompleteObjectGenerator. As a
    // result the objects are not properly marked as completed if the generation is induced by the value resolver
    // unless this is done here.
    // I however see no way to do that while still complying with the BC break policy, hence for 4.0.
    private function tryToMarkObjectAsComplete(FixtureInterface $fixture, ResolvedFixtureSet $set, GenerationContext $context): ResolvedFixtureSet
    {
        $object = $set->getObjects()->get($fixture);

        if ($object instanceof CompleteObject || false === $this->isObjectComplete($fixture, $set, $context)) {
            return $set;
        }

        return $set->withObjects(
            $set->getObjects()->with(
                new CompleteObject($object)
            )
        );
    }

    private function isObjectComplete(FixtureInterface $fixture, ResolvedFixtureSet $set, GenerationContext $context): bool
    {
        $object = $set->getObjects()->get($fixture);

        return (
            $object instanceof CompleteObject
            || $context->needsCompleteGeneration()
            || false === $context->isFirstPass()
            || (
                false === $context->needsCompleteGeneration()
                && $fixture->getSpecs()->getProperties()->isEmpty()
                && $fixture->getSpecs()->getMethodCalls()->isEmpty()
            )
        );
    }
}

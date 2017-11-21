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

use Nelmio\Alice\Definition\Object\CompleteObject;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ObjectGeneratorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ObjectInterface;

final class CompleteObjectGenerator implements ObjectGeneratorInterface
{
    use IsAServiceTrait;
    
    /**
     * @var ObjectGeneratorInterface
     */
    private $objectGenerator;

    public function __construct(ObjectGeneratorInterface $objectGenerator)
    {
        $this->objectGenerator = $objectGenerator;
    }

    /**
     * @inheritdoc
     */
    public function generate(
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        GenerationContext $context
    ): ObjectBag {
        if ($fixtureSet->getObjects()->has($fixture)
            && $fixtureSet->getObjects()->get($fixture) instanceof CompleteObject
        ) {
            return $fixtureSet->getObjects();
        }

        $objects = $this->objectGenerator->generate($fixture, $fixtureSet, $context);
        $generatedObject = $objects->get($fixture);

        if (false === $this->isObjectComplete($fixture, $generatedObject, $context)) {
            return $objects;
        }

        return $objects->with(new CompleteObject($generatedObject));
    }

    private function isObjectComplete(FixtureInterface $fixture, ObjectInterface $object, GenerationContext $context): bool
    {
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

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

namespace Nelmio\Alice\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Definition\Fixture\FixtureId;
use Nelmio\Alice\Definition\Object\CompleteObject;
use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureIdInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ObjectGeneratorAwareInterface;
use Nelmio\Alice\Generator\ObjectGeneratorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\Generator\ObjectGenerator\ObjectGeneratorNotFoundExceptionFactory;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\CircularReferenceException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\FixtureNotFoundExceptionFactory;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueExceptionFactory;

final class FixtureReferenceResolver implements ChainableValueResolverInterface, ObjectGeneratorAwareInterface
{
    use IsAServiceTrait;

    /**
     * @var ObjectGeneratorInterface|null
     */
    private $generator;

    /**
     * @var array
     */
    private $incompleteObjects = [];

    public function __construct(ObjectGeneratorInterface $generator = null)
    {
        $this->generator = $generator;
    }

    /**
     * @inheritdoc
     */
    public function withObjectGenerator(ObjectGeneratorInterface $generator): self
    {
        return new self($generator);
    }

    /**
     * @inheritdoc
     */
    public function canResolve(ValueInterface $value): bool
    {
        return $value instanceof FixtureReferenceValue;
    }

    /**
     * {@inheritdoc}
     *
     * @param FixtureReferenceValue $value
     *
     * @throws UnresolvableValueException
     */
    public function resolve(
        ValueInterface $value,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        array $scope,
        GenerationContext $context
    ): ResolvedValueWithFixtureSet {
        if (null === $this->generator) {
            throw ObjectGeneratorNotFoundExceptionFactory::createUnexpectedCall(__METHOD__);
        }

        $referredFixtureId = $value->getValue();
        if ($referredFixtureId instanceof ValueInterface) {
            throw UnresolvableValueExceptionFactory::create($value);
        }

        $referredFixture = $this->getReferredFixture($referredFixtureId, $fixtureSet);

        return $this->resolveReferredFixture($referredFixture, $referredFixtureId, $fixtureSet, $context);
    }

    private function getReferredFixture(string $id, ResolvedFixtureSet $set): FixtureIdInterface
    {
        $fixtures = $set->getFixtures();
        if ($fixtures->has($id)) {
            return $fixtures->get($id);
        }

        return new FixtureId($id);
    }

    /**
     * @param FixtureIdInterface|FixtureInterface $referredFixture
     * @param bool|null                           $passIncompleteObject
     */
    private function resolveReferredFixture(
        FixtureIdInterface $referredFixture,
        string $referredFixtureId,
        ResolvedFixtureSet $fixtureSet,
        GenerationContext $context,
        bool $passIncompleteObject = null
    ): ResolvedValueWithFixtureSet {
        if ($fixtureSet->getObjects()->has($referredFixture)) {
            $referredObject = $fixtureSet->getObjects()->get($referredFixture);

            if ($referredObject instanceof CompleteObject
                || $passIncompleteObject
                || array_key_exists($referredFixtureId, $this->incompleteObjects)
            ) {
                $this->incompleteObjects[$referredFixtureId] = true;

                return new ResolvedValueWithFixtureSet(
                    $referredObject->getInstance(),
                    $fixtureSet
                );
            }
        }

        // Object is either not completely generated or has not been generated at all yet
        // Attempts to generate the fixture completely
        if (false === $referredFixture instanceof FixtureInterface) {
            throw FixtureNotFoundExceptionFactory::create($referredFixtureId);
        }

        try {
            $needsCompleteGeneration = $context->needsCompleteGeneration();

            // Attempts to provide a complete object whenever possible
            $passIncompleteObject ? $context->unmarkAsNeedsCompleteGeneration() : $context->markAsNeedsCompleteGeneration();

            $context->markIsResolvingFixture($referredFixtureId);
            $objects = $this->generator->generate($referredFixture, $fixtureSet, $context);
            $fixtureSet =  $fixtureSet->withObjects($objects);

            // Restore the context
            $needsCompleteGeneration ? $context->markAsNeedsCompleteGeneration() : $context->unmarkAsNeedsCompleteGeneration();

            return new ResolvedValueWithFixtureSet(
                $fixtureSet->getObjects()->get($referredFixture)->getInstance(),
                $fixtureSet
            );
        } catch (CircularReferenceException $exception) {
            if (false === $needsCompleteGeneration && null !== $passIncompleteObject) {
                throw $exception;
            }

            $context->unmarkAsNeedsCompleteGeneration();

            // Could not completely generate the fixtures, fallback to generating an incomplete object
            return $this->resolveReferredFixture($referredFixture, $referredFixtureId, $fixtureSet, $context, true);
        }
    }
}

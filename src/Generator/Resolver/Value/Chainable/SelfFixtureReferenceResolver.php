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

use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ObjectGeneratorAwareInterface;
use Nelmio\Alice\Generator\ObjectGeneratorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueException;

final class SelfFixtureReferenceResolver implements ChainableValueResolverInterface, ObjectGeneratorAwareInterface, ValueResolverAwareInterface
{
    use IsAServiceTrait;

    /**
     * @var ChainableValueResolverInterface
     */
    private $decoratedResolver;

    public function __construct(ChainableValueResolverInterface $decoratedResolver)
    {
        $this->decoratedResolver = $decoratedResolver;
    }

    /**
     * @inheritdoc
     */
    public function withObjectGenerator(ObjectGeneratorInterface $generator): self
    {
        $decoratedResolver = ($this->decoratedResolver instanceof ObjectGeneratorAwareInterface)
            ? $this->decoratedResolver->withObjectGenerator($generator)
            : $this->decoratedResolver
        ;

        return new self($decoratedResolver);
    }

    /**
     * @inheritdoc
     */
    public function withValueResolver(ValueResolverInterface $resolver): self
    {
        $decoratedResolver = ($this->decoratedResolver instanceof ValueResolverAwareInterface)
            ? $this->decoratedResolver->withValueResolver($resolver)
            : $this->decoratedResolver
        ;

        return new self($decoratedResolver);
    }

    /**
     * @inheritdoc
     */
    public function canResolve(ValueInterface $value): bool
    {
        return $this->decoratedResolver->canResolve($value);
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
        if ('self' === $value->getValue()) {
            return new ResolvedValueWithFixtureSet(
                $fixtureSet->getObjects()->get($fixture)->getInstance(),
                $fixtureSet
            );
        }

        return $this->decoratedResolver->resolve($value, $fixture, $fixtureSet, $scope, $context);
    }
}

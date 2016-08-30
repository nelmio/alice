<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Exception\Generator\Resolver\UnresolvableValueException;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\ObjectGeneratorAwareInterface;
use Nelmio\Alice\Generator\ObjectGeneratorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\NotClonableTrait;

final class SelfFixtureReferenceResolver
implements ChainableValueResolverInterface, ObjectGeneratorAwareInterface, ValueResolverAwareInterface
{
    use NotClonableTrait;

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
    public function withGenerator(ObjectGeneratorInterface $generator): self
    {
        if ($this->decoratedResolver instanceof ObjectGeneratorAwareInterface) {
            $this->decoratedResolver = $this->decoratedResolver->withGenerator($generator);
        }
        return new self($this->decoratedResolver);
    }

    /**
     * @inheritdoc
     */
    public function withResolver(ValueResolverInterface $resolver): self
    {
        if ($this->decoratedResolver instanceof ValueResolverAwareInterface) {
            $this->decoratedResolver = $this->decoratedResolver->withResolver($resolver);
        }
        return new self($this->decoratedResolver);
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
        array $scope = []
    ): ResolvedValueWithFixtureSet
    {
        if ('self' === $value->getValue()) {
            return new ResolvedValueWithFixtureSet(
                $fixtureSet->getObjects()->get($fixture)->getInstance(),
                $fixtureSet
            );
        }

        return $this->decoratedResolver->resolve($value, $fixture, $fixtureSet, $scope);

    }
}

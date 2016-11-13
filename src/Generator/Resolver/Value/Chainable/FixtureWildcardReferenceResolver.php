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

use Faker\Provider\Base;
use Nelmio\Alice\Definition\Value\FixtureMatchReferenceValue;
use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundException;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ObjectInterface;

final class FixtureWildcardReferenceResolver implements ChainableValueResolverInterface, ValueResolverAwareInterface
{
    use IsAServiceTrait;

    /**
     * @var ValueResolverInterface
     */
    private $resolver;

    private $idsByPattern = [];

    public function __construct(ValueResolverInterface $resolver = null)
    {
        $this->resolver = $resolver;
    }

    /**
     * @inheritdoc
     */
    public function withValueResolver(ValueResolverInterface $resolver): self
    {
        return new self($resolver);
    }

    /**
     * @inheritdoc
     */
    public function canResolve(ValueInterface $value): bool
    {
        return $value instanceof FixtureMatchReferenceValue;
    }

    /**
     * {@inheritdoc}
     *
     * @param FixtureMatchReferenceValue $value
     *
     * @throws UnresolvableValueException
     */
    public function resolve(
        ValueInterface $value,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        array $scope,
        GenerationContext $context
    ): ResolvedValueWithFixtureSet
    {
        if (null === $this->resolver) {
            throw ResolverNotFoundException::createUnexpectedCall(__METHOD__);
        }

        $possibleIds = $this->getSuitableIds($value, $fixtureSet);
        $id = Base::randomElement($possibleIds);
        if (null === $id) {
            throw UnresolvableValueException::createForNoFixtureOrObjectMatchingThePattern($value);
        }

        return $this->resolver->resolve(
            new FixtureReferenceValue($id),
            $fixture,
            $fixtureSet,
            $scope,
            $context
        );
    }

    /**
     * Gets all the fixture IDs suitable for the given value.
     *
     * @param FixtureMatchReferenceValue $value
     * @param ResolvedFixtureSet         $fixtureSet
     *
     * @return string[]
     */
    private function getSuitableIds(FixtureMatchReferenceValue $value, ResolvedFixtureSet $fixtureSet): array
    {
        if (array_key_exists($pattern = $value->getValue(), $this->idsByPattern)) {
            return $this->idsByPattern[$pattern];
        }

        $fixtureKeys = array_flip(
            preg_grep(
                $pattern,
                array_keys($fixtureSet->getFixtures()->toArray())
            )
        );
        $objectKeys = array_flip(
            preg_grep(
                $pattern,
                array_keys($fixtureSet->getObjects()->toArray())
            )
        );

        $this->idsByPattern[$pattern] = array_keys($fixtureKeys + $objectKeys);

        return $this->idsByPattern[$pattern];
    }
}

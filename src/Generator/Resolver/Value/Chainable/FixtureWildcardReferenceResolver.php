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
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\Generator\Context\CachedValueNotFound;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundExceptionFactory;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueExceptionFactory;

final class FixtureWildcardReferenceResolver implements ChainableValueResolverInterface, ValueResolverAwareInterface
{
    use IsAServiceTrait;

    /**
     * @var ValueResolverInterface
     */
    private $resolver;

    /** @private */
    const IDS_BY_PATTERN_CACHE_KEY = self::class;

    public function __construct(ValueResolverInterface $resolver = null)
    {
        $this->resolver = $resolver;
    }
    
    public function withValueResolver(ValueResolverInterface $resolver): self
    {
        return new self($resolver);
    }
    
    public function canResolve(ValueInterface $value): bool
    {
        return $value instanceof FixtureMatchReferenceValue;
    }

    /**
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
    ): ResolvedValueWithFixtureSet {
        if (null === $this->resolver) {
            throw ResolverNotFoundExceptionFactory::createUnexpectedCall(__METHOD__);
        }

        $possibleIds = $this->getSuitableIds($value, $fixtureSet, $context);
        $id = Base::randomElement($possibleIds);
        if (null === $id) {
            throw UnresolvableValueExceptionFactory::createForNoFixtureOrObjectMatchingThePattern($value);
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
     * @return string[]
     */
    private function getSuitableIds(
        FixtureMatchReferenceValue $value,
        ResolvedFixtureSet $fixtureSet,
        GenerationContext $context
    ): array {
        $pattern = $value->getValue();

        try {
            $cache = $context->getCachedValue(self::IDS_BY_PATTERN_CACHE_KEY);
        } catch (CachedValueNotFound $exception) {
            $cache = [];
        }

        if (array_key_exists($pattern, $cache)) {
            return $cache[$pattern];
        }

        $suitableIds = $this->findSuitableIds($pattern, $fixtureSet);

        $cache[$pattern] = $suitableIds;
        $context->cacheValue(self::IDS_BY_PATTERN_CACHE_KEY, $cache);

        return $suitableIds;
    }

    /**
     * @return string[]
     */
    private function findSuitableIds(string $pattern, ResolvedFixtureSet $fixtureSet): array
    {
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

        return array_keys($fixtureKeys + $objectKeys);
    }
}

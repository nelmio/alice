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

use Faker\Generator as FakerGenerator;
use Nelmio\Alice\Definition\Value\FunctionCallValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Exception\Generator\Resolver\ResolverNotFoundException;
use Nelmio\Alice\Faker\GeneratorFactory;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\NotClonableTrait;

final class FakerFunctionCallValueResolver implements ChainableValueResolverInterface, ValueResolverAwareInterface
{
    use NotClonableTrait;

    /**
     * @var GeneratorFactory
     */
    private $generatorFactory;

    /**
     * @var ValueResolverInterface
     */
    private $resolver;

    public function __construct(FakerGenerator $fakerGenerator, ValueResolverInterface $resolver = null)
    {
        $this->generatorFactory = new GeneratorFactory($fakerGenerator);
        $this->resolver = $resolver;
    }

    /**
     * @inheritdoc
     */
    public function withValueResolver(ValueResolverInterface $resolver): self
    {
        return new self($this->generatorFactory->getSeedGenerator(), $resolver);
    }

    /**
     * @inheritdoc
     */
    public function canResolve(ValueInterface $value): bool
    {
        return $value instanceof FunctionCallValue;
    }

    /**
     * {@inheritdoc}
     *
     * @param FunctionCallValue $value
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

        $arguments = $value->getArguments();
        foreach ($arguments as $index => $argument) {
            if ($argument instanceof ValueInterface) {
                $resolvedSet = $this->resolver->resolve($argument, $fixture, $fixtureSet, $scope, $context);

                $arguments[$index] = $resolvedSet->getValue();
                $fixtureSet = $resolvedSet->getSet();
            }
        }

        /**
         * @var FakerGenerator $generator
         * @var string         $formatter
         */
        list($generator, $formatter) = $this->getGenerator($this->generatorFactory, $value->getName());

        return new ResolvedValueWithFixtureSet(
            $generator->format($formatter, $arguments),
            $fixtureSet
        );
    }

    private function getGenerator(GeneratorFactory $factory, string $formatter)
    {
        $explodedFormatter = explode(':', $formatter);
        $size = count($explodedFormatter);

        if (1 === $size) {
            return [$factory->getSeedGenerator(), $explodedFormatter[0]];
        }

        if (2 === $size) {
            return [
                $factory->createOrReturnExistingInstance($explodedFormatter[0]),
                $explodedFormatter[1]
            ];
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Invalid faker formatter "%s" found.',
                $formatter
            )
        );
    }
}

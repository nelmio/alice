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
use Nelmio\Alice\Definition\Value\ResolvedFunctionCallValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Faker\GeneratorFactory;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\InvalidArgumentExceptionFactory;

final class FakerFunctionCallValueResolver implements ChainableValueResolverInterface
{
    use IsAServiceTrait;

    /**
     * @var GeneratorFactory
     */
    private $generatorFactory;

    public function __construct(FakerGenerator $fakerGenerator)
    {
        $this->generatorFactory = new GeneratorFactory($fakerGenerator);
    }

    /**
     * @inheritdoc
     */
    public function canResolve(ValueInterface $value): bool
    {
        return $value instanceof ResolvedFunctionCallValue;
    }

    /**
     * {@inheritdoc}
     *
     * @param ResolvedFunctionCallValue $value
     */
    public function resolve(
        ValueInterface $value,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        array $scope,
        GenerationContext $context
    ): ResolvedValueWithFixtureSet {
        /**
         * @var FakerGenerator $generator
         * @var string         $formatter
         */
        list($generator, $formatter) = $this->getGenerator($this->generatorFactory, $value->getName());

        return new ResolvedValueWithFixtureSet(
            $generator->format($formatter, $value->getArguments()),
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

        throw InvalidArgumentExceptionFactory::createForInvalidFakerFormatter($formatter);
    }
}

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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Arguments;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ArgumentsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\NotClonableTrait;

final class NamedArgumentsDenormalizer implements ArgumentsDenormalizerInterface
{
    use NotClonableTrait;

    /**
     * @var ArgumentsDenormalizerInterface
     */
    private $argumentsDenormalizer;

    public function __construct(ArgumentsDenormalizerInterface $argumentsDenormalizer)
    {
        $this->argumentsDenormalizer = $argumentsDenormalizer;
    }

    /**
     * @inheritdoc
     */
    public function denormalize(
        FixtureInterface $scope,
        FlagParserInterface $parser,
        array $unparsedArguments
    ): array
    {
        $arguments = $this->argumentsDenormalizer->denormalize($scope, $parser, $unparsedArguments);
        if (false === $this->arrayHasOnlyNumericalKeys($arguments)) {
            $arguments = $this->reorganizeArguments($scope, $arguments);
        }
        ksort($arguments);

        return $arguments;
    }

    private function arrayHasOnlyNumericalKeys(array $arguments): bool
    {
        foreach ($arguments as $key => $argument) {
            if (false === is_numeric($key)) {
                return false;
            }
        }

        return true;
    }

    private function reorganizeArguments(FixtureInterface $fixture, array $arguments): array
    {
        $constructorRefl = (new \ReflectionClass($fixture->getClassName()))->getMethod('__construct');
        $parameters = $constructorRefl->getParameters();

        $orderedArguments = [];
        foreach ($parameters as $parameter) {
            if (array_key_exists($parameterName = $parameter->getName(), $arguments)) {
                $orderedArguments[$parameter->getPosition()] = $arguments[$parameterName];
                continue;
            }

            if (array_key_exists($position = $parameter->getPosition(), $arguments)) {
                $orderedArguments[$position] = $arguments[$position];
                continue;
            }

            if (false === $parameter->isDefaultValueAvailable()) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'The parameter "%s" for "%s" (%s) is required but no value was given.',
                        $parameter->getName(),
                        $fixture->getId(),
                        $fixture->getClassName()
                    )
                );
            }

            $orderedArguments[$position] = $parameter->getDefaultValue();
        }

        return $orderedArguments;
    }
}

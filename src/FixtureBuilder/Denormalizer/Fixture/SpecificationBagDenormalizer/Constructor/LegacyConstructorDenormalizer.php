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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor;

use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ConstructorDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\UnexpectedValueException;

final class LegacyConstructorDenormalizer implements ConstructorDenormalizerInterface
{
    use IsAServiceTrait;

    /**
     * @var ConstructorDenormalizerInterface
     */
    private $constructorDenormalizer;

    /**
     * @var ConstructorDenormalizerInterface
     */
    private $factoryDenormalizer;

    public function __construct(
        ConstructorDenormalizerInterface $constructorDenormalizer,
        ConstructorDenormalizerInterface $factoryDenormalizer
    ) {
        $this->constructorDenormalizer = $constructorDenormalizer;
        $this->factoryDenormalizer = $factoryDenormalizer;
    }
    
    public function denormalize(
        FixtureInterface $scope,
        FlagParserInterface $parser,
        array $unparsedConstructor
    ): MethodCallInterface {
        try {
            return $this->factoryDenormalizer->denormalize($scope, $parser, $unparsedConstructor);
        } catch (UnexpectedValueException $exception) {
            // Continue
        }

        return $this->constructorDenormalizer->denormalize($scope, $parser, $unparsedConstructor);
    }
}

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
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\CallsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ConstructorDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\DenormalizerExceptionFactory;

final class FactoryDenormalizer implements ConstructorDenormalizerInterface
{
    use IsAServiceTrait;

    /**
     * @var CallsDenormalizerInterface
     */
    private $callsDenormalizer;

    public function __construct(CallsDenormalizerInterface $callsDenormalizer)
    {
        $this->callsDenormalizer = $callsDenormalizer;
    }

    /**
     * @inheritdoc
     */
    public function denormalize(
        FixtureInterface $scope,
        FlagParserInterface $parser,
        array $unparsedMethod
    ): MethodCallInterface {
        /** @var string|bool $method */
        $method = key($unparsedMethod);

        if (false === $method
            || false === is_string($method)
            || 1 !== count($unparsedMethod)
        ) {
            throw DenormalizerExceptionFactory::createForUndenormalizableFactory();
        }

        $arguments = $unparsedMethod[$method];

        if (false === is_array($arguments)) {
            throw DenormalizerExceptionFactory::createForUndenormalizableFactory();
        }

        if (false === strpos($method, '::')) {
            $method = sprintf(
                '%s::%s',
                $scope->getClassName(),
                $method
            );
        }

        return $this->callsDenormalizer->denormalize(
            $scope,
            $parser,
            $method,
            $arguments
        );
    }
}

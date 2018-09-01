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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Value;

use Nelmio\Alice\Definition\Flag\UniqueFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\Value\ArrayValue;
use Nelmio\Alice\Definition\Value\DynamicArrayValue;
use Nelmio\Alice\Definition\Value\UniqueValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ValueDenormalizerInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\DenormalizerExceptionFactory;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\InvalidScopeException;
use function random_bytes;

final class UniqueValueDenormalizer implements ValueDenormalizerInterface
{
    use IsAServiceTrait;

    /**
     * @var ValueDenormalizerInterface
     */
    private $denormalizer;

    public function __construct(ValueDenormalizerInterface $decoratedDenormalizer)
    {
        $this->denormalizer = $decoratedDenormalizer;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidScopeException
     */
    public function denormalize(FixtureInterface $scope, FlagBag $flags = null, $value)
    {
        $value = $this->denormalizer->denormalize($scope, $flags, $value);

        if (false === $this->requiresUnique($flags)) {
            return $value;
        }

        return $this->generateValue($scope, $flags, $value);
    }

    private function requiresUnique(FlagBag $flags = null): bool
    {
        if (null === $flags) {
            return false;
        }

        foreach ($flags as $flag) {
            if ($flag instanceof UniqueFlag) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed|ValueInterface $value
     *
     * @throws InvalidScopeException
     */
    private function generateValue(FixtureInterface $scope, FlagBag $flags, $value): ValueInterface
    {
        $uniqueId = sprintf('%s#%s', $scope->getClassName(), $flags->getKey());
        if ('temporary_id' === substr($scope->getId(), 0, 12)) {
            throw DenormalizerExceptionFactory::createForInvalidScopeForUniqueValue();
        }

        if ($value instanceof DynamicArrayValue) {
            $uniqueId = $uniqueId.'::__array_element_id#'.bin2hex(random_bytes(16));

            return new DynamicArrayValue(
                $value->getQuantifier(),
                new UniqueValue($uniqueId, $value->getElement())
            );
        }

        if ($value instanceof ArrayValue) {
            $uniqueId = uniqid($uniqueId.'::', true);
            $elements = $value->getValue();

            foreach ($elements as $key => $element) {
                // The key must be the same for each argument: the unique ID is bound to the array, not the argument
                // number.
                $elements[$key] = new UniqueValue($uniqueId, $element);
            }

            return new ArrayValue($elements);
        }

        return new UniqueValue($uniqueId, $value);
    }
}

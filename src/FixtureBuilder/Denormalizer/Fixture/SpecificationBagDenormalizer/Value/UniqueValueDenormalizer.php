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
use Nelmio\Alice\NotClonableTrait;

final class UniqueValueDenormalizer implements ValueDenormalizerInterface
{
    use NotClonableTrait;

    /**
     * @var ValueDenormalizerInterface
     */
    private $denormalizer;

    public function __construct(ValueDenormalizerInterface $decoratedDenormalizer)
    {
        $this->denormalizer = $decoratedDenormalizer;
    }

    /**
     * @inheritdoc
     */
    public function denormalize(FixtureInterface $scope, FlagBag $flags = null, $value)
    {
       $value = $this->denormalizer->denormalize($scope, $flags, $value);

        if (false === $this->requiresUnique($flags)) {
            return $value;
        }
        $uniqueId = sprintf('%s#%s', $scope->getClassName(), $flags->getKey());

        return $this->generateValue($uniqueId, $value);
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
     * @param string               $uniqueId
     * @param mixed|ValueInterface $value
     *
     * @return ValueInterface
     */
    private function generateValue(string $uniqueId, $value): ValueInterface
    {
        if ($value instanceof DynamicArrayValue) {
            return new DynamicArrayValue(
                $value->getQuantifier(),
                new UniqueValue($uniqueId, $value->getElement())
            );
        }

        if ($value instanceof ArrayValue) {
            $elements = $value->getValue();
            foreach ($elements as $key => $element) {
                $elements[$key] = new UniqueValue($uniqueId, $element);
            }

            return new ArrayValue($elements);
        }

        return new UniqueValue($uniqueId, $value);
    }
}

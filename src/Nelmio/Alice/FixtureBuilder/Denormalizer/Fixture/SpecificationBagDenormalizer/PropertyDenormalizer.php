<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer;

use Nelmio\Alice\Definition\Flag\UniqueFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Definition\Value\UniqueValue;
use Nelmio\Alice\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\NotClonableTrait;

final class PropertyDenormalizer
{
    use NotClonableTrait;

    /**
     * @var ParserInterface
     */
    private $parser;

    public function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Denormalizes a property.
     *
     * @param FixtureInterface $scope See SpecificationsDenormalizerInterface::denormalize()
     * @param string           $name
     * @param mixed            $value
     * @param FlagBag          $flags
     *
     * @return Property
     */
    public function denormalize(FixtureInterface $scope, string $name, $value, FlagBag $flags): Property
    {
        if (is_string($value)) {
            $value = $this->parser->parse($value);
        }

        foreach ($flags as $flag) {
            if ($flag instanceof UniqueFlag) {
                $uniqueId = $scope->getId().'::'.$name;

                return new Property($name, new UniqueValue($uniqueId, $value));
            }
        }

        return new Property($name, $value);
    }
}

<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Value;

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\Value\ArrayValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\UnexpectedValueException;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ValueDenormalizerInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\Throwable\ParseThrowable;

final class SimpleValueDenormalizer implements ValueDenormalizerInterface
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
     * @inheritdoc
     */
    public function denormalize(FixtureInterface $scope, FlagBag $flags = null, $value)
    {
        if (is_string($value)) {
            return $this->parseValue($this->parser, $value);
        }

        if (is_array($value)) {
            $array = [];
            foreach ($value as $key => $item) {
                $array[$key] = $this->denormalize($scope, $flags, $item);
            }

            return new ArrayValue($array);
        }

        return $value;
    }

    /**
     * @param ParserInterface $parser
     * @param string          $value
     *
     * @return mixed|ValueInterface
     */
    private function parseValue(ParserInterface $parser, string $value)
    {
        try {
            return $parser->parse($value);
        } catch (ParseThrowable $throwable) {
            throw new UnexpectedValueException(
                sprintf(
                    'Could not parse value "%s".',
                    $value
                ),
                0,
                $throwable
            );
        }
    }
}

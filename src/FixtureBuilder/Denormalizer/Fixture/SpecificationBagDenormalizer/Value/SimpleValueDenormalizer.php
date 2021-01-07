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

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\Value\ArrayValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ValueDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\DenormalizerExceptionFactory;
use Nelmio\Alice\Throwable\ParseThrowable;

final class SimpleValueDenormalizer implements ValueDenormalizerInterface
{
    use IsAServiceTrait;

    /**
     * @var ParserInterface
     */
    private $parser;

    public function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
    }
    
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
     * @return mixed|ValueInterface
     */
    private function parseValue(ParserInterface $parser, string $value)
    {
        try {
            return $parser->parse($value);
        } catch (ParseThrowable $throwable) {
            throw DenormalizerExceptionFactory::createForUnparsableValue($value, 0, $throwable);
        }
    }
}

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

use Nelmio\Alice\Definition\Flag\UniqueFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\Value\DynamicArrayValue;
use Nelmio\Alice\Definition\Value\UniqueValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\UnexpectedValueException;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ValueDenormalizerInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\Throwable\ParseThrowable;

final class UniqueValueDenormalizer implements ValueDenormalizerInterface
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
            $value = $this->parseValue($this->parser, $value);
        }

        if (false === $this->requiresUnique($flags)) {
            return $value;
        }

        $uniqueId = uniqid($scope->getId());
        if ($value instanceof DynamicArrayValue) {
            return new DynamicArrayValue(
                $value->getQuantifier(),
                new UniqueValue($uniqueId, $value->getValue())
            );
        }

        return new UniqueValue($uniqueId, $value);
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
                    0,
                    $throwable
                )
            );
        }
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
}

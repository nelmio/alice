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
use Nelmio\Alice\Definition\Value\DynamicArrayValue;
use Nelmio\Alice\Definition\Value\UniqueValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;

/**
 * Denormalizer for a function call arguments.
 */
class ArgumentsDenormalizer
{
    /**
     * @var ParserInterface
     */
    private $parser;

    public function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Denormalizes an array of arguments.
     *
     * @param FixtureInterface    $scope
     * @param FlagParserInterface $parser
     * @param array               $unparsedArguments
     *
     * @return array|ValueInterface[]
     *
     * @example
     *  example1:
     *  $unparsedArguments = [
     *      '<latitude()>',
     *      '<longitude()>',
     *  ],
     *
     *  example2:
     *  $unparsedArguments = [
     *      '0 (unique) => '<latitude()>',
     *      1 => '<longitude()>',
     *  ],
     */
    public final function denormalize(
        FixtureInterface $scope,
        FlagParserInterface $parser,
        array $unparsedArguments
    ): array
    {
        $arguments = [];
        foreach ($unparsedArguments as $unparsedIndex => $argument) {
            $argumentFlags = (is_string($unparsedIndex)) ? $parser->parse($unparsedIndex) : null;
            $arguments[] = $this->handleArgumentFlags($scope, $argumentFlags, $argument);
        }

        return $arguments;
    }

    /**
     * @param FixtureInterface $scope See SpecificationsDenormalizerInterface::denormalize()
     * @param FlagBag|null     $flags
     * @param mixed            $argument
     *
     * @return mixed|ValueInterface
     */
    protected function handleArgumentFlags(FixtureInterface $scope, FlagBag $flags = null, $argument)
    {
        if (is_string($argument)) {
            $argument = $this->parser->parse($argument);
        }
        if (null === $flags) {
            return $argument;
        }

        if ($this->requiresUnique($flags)) {
            $uniqueId = uniqid($scope->getId());

            if ($argument instanceof DynamicArrayValue) {
                return new DynamicArrayValue(
                    $argument->getQuantifier(),
                    new UniqueValue($uniqueId, $argument->getValue())
                );
            }
            return new UniqueValue($uniqueId, $argument);
        }
        
        return $argument;
    }

    private function requiresUnique(FlagBag $flags): bool
    {
        foreach ($flags as $flag) {
            if ($flag instanceof UniqueFlag) {
                return true;
            }
        }

        return false;
    }
}

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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser;

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\FlagParser\FlagParserExceptionFactory;

/**
 * Delegates the responsibility to the first suitable parser found.
 */
final class FlagParserRegistry implements FlagParserInterface
{
    use IsAServiceTrait;

    /**
     * @var ChainableFlagParserInterface[]
     */
    private $parsers;

    /**
     * @param ChainableFlagParserInterface[] $parsers
     */
    public function __construct(array $parsers)
    {
        $this->parsers = (
            function (ChainableFlagParserInterface ...$parsers) {
                return $parsers;
            }
        )(...$parsers);
    }

    /**
     * @inheritdoc
     */
    public function parse(string $element): FlagBag
    {
        foreach ($this->parsers as $parser) {
            if ($parser->canParse($element)) {
                return $parser->parse($element);
            }
        }

        throw FlagParserExceptionFactory::createForNoParserFoundForElement($element);
    }
}

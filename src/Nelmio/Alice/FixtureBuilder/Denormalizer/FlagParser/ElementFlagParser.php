<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser;

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;

/**
 * Extract flag elements from a given string and delegates the parsing of each element to the decorated parser.
 */
final class ElementFlagParser implements FlagParserInterface
{
    /**
     * @var FlagParserInterface
     */
    private $parser;

    /**
     * @param FlagParserInterface $parser
     */
    public function __construct(FlagParserInterface $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @inheritdoc
     */
    public function parse(string $element): FlagBag
    {
        if (1 !== preg_match('/\s*(?<reference>.+?)\s\((?<stringFlags>.+).*\)$/', $element, $matches)) {
            return new FlagBag($element);
        }

        $flags = new FlagBag($matches['reference']);
        $stringFlags = preg_split('/\s*,\s*/', $matches['stringFlags']);
        foreach ($stringFlags as $stringFlag) {
            $flags = $flags->mergeWith(
                $this->parser->parse(trim($stringFlag))
            );
        }

        return $flags;
    }
}

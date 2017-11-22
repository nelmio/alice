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

/**
 * Extracts flag elements from a given string and delegates the parsing of each element to the decorated parser.
 */
final class ElementFlagParser implements FlagParserInterface
{
    use IsAServiceTrait;

    /** @private */
    const REGEX = '/\s*(?<reference>.+?)\s\((?<stringFlags>.+).*\)$/';

    /**
     * @var FlagParserInterface
     */
    private $parser;

    public function __construct(FlagParserInterface $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @inheritdoc
     */
    public function parse(string $element): FlagBag
    {
        if (1 !== preg_match(self::REGEX, $element, $matches)) {
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

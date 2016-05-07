<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Parser;

use Nelmio\Alice\Fixtures\Parser\Methods\MethodInterface;

class Parser
{
    /**
     * @var MethodInterface[]
     */
    private $parsers = [];

    /**
     * @param MethodInterface[] $parsers
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $parsers)
    {
        foreach ($parsers as $parser) {
            if (!($parser instanceof MethodInterface)) {
                throw new \InvalidArgumentException("All parsers passed into Parser must implement MethodInterface.");
            }
        }

        $this->parsers = $parsers;
    }

    /**
     * Adds a parser for parsing files.
     *
     * @param MethodInterface $parser
     **/
    public function addParser(MethodInterface $parser)
    {
        array_unshift($this->parsers, $parser);
    }

    /**
     * Parses the given file and returns an array of data.
     *
     * @param string $file File path
     *
     * @throws \UnexpectedValueException
     *
     * @return array|null
     */
    public function parse($file)
    {
        foreach ($this->parsers as $parser) {
            if ($parser->canParse($file)) {
                return $parser->parse($file);
            }
        }

        throw new \UnexpectedValueException("{$file} cannot be parsed - no parser exists that can handle it.");
    }
}

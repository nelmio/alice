<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Builder\FlagParser;

use Nelmio\Alice\Builder\Flag\ExtendsFlag;
use Nelmio\Alice\Builder\Flag\TemplateFlag;
use Nelmio\Alice\Builder\FlagParserInterface;

final class TemplatingParser implements FlagParserInterface
{
    /**
     * @var SanitizeParser
     */
    private $parser;

    public function __construct(SanitizeParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @inheritdoc
     */
    public function parse(string $key): array
    {
        $rawFlags = $this->parser->parse($key);
        $flags = [];
        foreach ($rawFlags as $rawFlag) {
            if ('extends ' === substr($rawFlag, 0, 8)) {
                $flags[] = new ExtendsFlag(substr($rawFlag, 8));

                continue;
            }

            if ('template' === $rawFlag) {
                $flags[] = new TemplateFlag(substr($rawFlag, 8));

                continue;
            }
        }
        
        return $flags;
    }
}

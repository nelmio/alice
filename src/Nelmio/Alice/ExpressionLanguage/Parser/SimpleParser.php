<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\ExpressionLanguage\Parser;

use Nelmio\Alice\Definition\Value\ValueList;
use Nelmio\Alice\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\ExpressionLanguage\ParserAwareInterface;
use Nelmio\Alice\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\ExpressionLanguage\TokenParserInterface;
use Nelmio\Alice\NotClonableTrait;

final class SimpleParser implements ParserInterface
{
    use NotClonableTrait;
    
    /**
     * @var LexerInterface
     */
    private $lexer;

    /**
     * @var TokenParserInterface
     */
    private $tokenParser;

    public function __construct(LexerInterface $lexer, TokenParserInterface $tokenParser)
    {
        $this->lexer = $lexer;
        $this->tokenParser = ($tokenParser instanceof ParserAwareInterface)
            ? $tokenParser->with($this)
            : $tokenParser
        ;
    }
    
    /**
     * @inheritdoc
     */
    public function parse(string $value)
    {
        $tokens = $this->lexer->lex($value);
        foreach ($tokens as $index => $token) {
            $tokens[$index] = $this->tokenParser->parse($token);
        }
        
        if (1 === count($tokens)) {
            return $tokens[0];
        }
        
        return new ValueList($tokens);
    }
}

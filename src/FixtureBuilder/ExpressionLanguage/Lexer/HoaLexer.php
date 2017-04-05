<?php
declare(strict_types=1);

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer;

use Hoa\Compiler\Llk\Parser as HoaParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\LexerInterface;

final class HoaLexer implements LexerInterface
{
    /**
     * @var HoaParser
     */
    private $parser;

    public function __construct(HoaParser $parser)
    {

        $this->parser = $parser;
    }

    /**
     * @inheritdoc
     */
    public function lex(string $value)
    {
        return $this->parser->parse($value);
    }
}
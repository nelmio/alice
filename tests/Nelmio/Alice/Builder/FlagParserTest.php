<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Builder;

/**
 * @covers Nelmio\Alice\Builder\FlagParser
 */
class FlagParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FlagParser
     */
    private $parser;

    public function setUp()
    {
        $this->parser = new FlagParser();
    }

    /**
     * @dataProvider provideKeys
     */
    public function test_parse(string $key, array $expected)
    {
        $actual = $this->parser->parse($key);

        $this->assertSame($expected, $actual);
    }

    public function provideKeys()
    {
        return [
            'basic complete sample' => [
                'user0 (template, extends user_base)',
                [
                    'template' => true,
                    'extends user_base' => true,
                ]
            ],
            'without spacing' => [
                'user0(template,extends user_base)',
                [
                    'template' => true,
                    'extends user_base' => true,
                ]
            ],
            'with spacing' => [
                'user0 (  template  , extends user_base  )',
                [
                    'template' => true,
                    'extends user_base' => true,
                ]
            ],
            'with ending comma' => [
                'user0 (template,)',
                [
                    'template' => true,
                ]
            ],
        ];
    }
}

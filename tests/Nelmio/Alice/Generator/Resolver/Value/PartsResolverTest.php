<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Value;

/**
 * @covers Nelmio\Alice\Generator\Resolver\Value\PartsResolver
 */
class PartsResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Here is a list of possible patterns the resolver must be able to detect to split the string value accordingly.
     * Unless specified otherwise, X and Y are a group of characters (any) or an empty string.
     *
     *  - P1: '<X>'
     *  - P2: [X];recursive on each element
     *  - P3: 'Xx Y'; X: number|'<X>', Y: *
     *  - P4: 'X%? Y' or 'X%? Y: Z'; X: number|'<X>', Y: [^:]*; Z: *
     *  - P5: "@X": delimited on the right by ø or a space
     *  - P6: '$X'
     *
     * @link https://github.com/nelmio/alice/issues/377
     *
     * @return array|\Generator
     */
    public function provideValues()
    {
        // simple values
        yield 'null value' => [
            1,
            null,
        ];
        yield 'int value' => [
            1,
            null,
        ];
        yield 'float value' => [
            1.1,
            null,
        ];
        yield 'regular string value' => [
            'dummy',
            [
                'dummy',
            ],
        ];
        yield 'string array' => [
            '[dummy]',
            [
                '[dummy]',
            ],
        ];

        // array values: apply itself recursively
        yield 'array of strings' => [
            [
                'foo',
                'bar',
                'baz',
            ],
            [
                'dummy',
            ],
        ];

        // P1
        yield '[P1] alone' => [
            '<string_value>',
            [
                '<string_value>',
            ],
        ];
        yield '[P1] surrounded' => [
            'foo <string_value> bar',
            [
                'foo' ,
                '<string_value>',
                ' bar',
            ],
        ];
        yield '[P1] surrounded left' => [
            'foo <string_value>',
            [
                'foo ',
                '<string_value>',
            ],
        ];
        yield '[P1] surrounded right' => [
            '<string_value> bar',
            [
                '<string_value>',
                ' bar',
            ],
        ];

        // P3
        yield '[P3] alone' => [
            '80x Y',
            [
                '80x Y',
            ],
        ];
        yield '[P3] alone' => [
            '<dummy>x Y',
            [
                '<dummy>x Y',
            ],
        ];
        yield '[P3] alone with negative number' => [
            '-50x Y',
            [
                '-',
                '-50x Y',
            ],
        ];
        yield '[P3] left with strings' => [
            'foo 100x Y',
            [
                'foo ',
                'Xx Y',
            ],
        ];
        yield '[P3] left with param' => [
            'foo <current()>x Y',
            [
                'foo ',
                '<current()>x Y',
            ],
        ];
        yield '[P3] right with strings' => [
            '10x Y bar',
            [
                '10x Y',
                ' bar',
            ],
        ];

        // P4
        yield '[P4] alone' => [
            '100%? Y',
            [
                '100%? Y',
            ],
        ];
        yield '[P4] alone with negative number' => [
            '-100%? Y',
            [
                '-',
                '100%? Y',
            ],
        ];
        yield '[P4] alone with negative number' => [
            '-100%? Y',
            [
                '-',
                '100%? Y',
            ],
        ];
        yield '[P4] alone with scalars' => [
            '80%? dummy',
            [
                '80%? dummy',
            ],
        ];
        yield '[P4] left with strings' => [
            'foo X%? Y',
            [
                'foo X%? Y',
            ],
        ];
        yield '[P4] right with strings' => [
            'X%? Y bar',
            [
                'X%? Y bar',
            ],
        ];

        // P5
        yield '[P5] alone with strings' => [
            '@user0',
            [
                '@user0',
            ],
        ];
        yield '[P5] left with strings' => [
            'foo @user0',
            [
                'foo ',
                '@user0',
            ],
        ];
        yield '[P5] right with strings' => [
            '@user0 bar',
            [
                '@user0',
                ' bar',
            ],
        ];
        yield '[P5] alone with prop' => [
            '@user0->username',
            [
                '@user0->username',
            ],
        ];
        yield '[P5] left with prop' => [
            'foo @user0->username',
            [
                'foo ',
                '@user0->username',
            ],
        ];
        yield '[P5] right with prop' => [
            '@user0->username bar',
            [
                '@user0->username',
                ' bar',
            ],
        ];
        yield '[P5] alone with function' => [
            '@user0->getUserName()',
            [
                '@user0->getUserName()',
            ],
        ];
        yield '[P5] left with function' => [
            'foo @user0->getUserName()',
            [
                ' foo',
                '@user0->getUserName()',
            ],
        ];
        yield '[P5] right with function' => [
            '@user0->getUserName() bar',
            [
                '@user0->getUserName()',
                ' bar',
            ],
        ];

        yield '[P6] alone' => [
            '$username',
            [
                '$username',
            ],
        ];
        yield '[P6] left' => [
            'foo $username',
            [
                'foo ',
                '$username',
            ],
        ];
        yield '[P6] right' => [
            '$username bar',
            [
                '$username',
                ' bar',
            ],
        ];

        yield '[P1][P3] next 1' => [
            '<string_value> foo Xx Y',
            [
                'foo',
                '<string_value>',
                'bar',
            ],
        ];
    }
}

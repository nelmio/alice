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

namespace Nelmio\Alice\Definition;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Definition\RangeName
 */
class RangeNameTest extends TestCase
{
    /**
     * @dataProvider provideRanges
     */
    public function testReadAccessorsReturnPropertiesValues(array $input, array $expected)
    {
        $name = 'user';
        list($from, $to) = $input;
        
        $range = new RangeName($name, $from, $to);
        
        $this->assertEquals($name, $range->getName());
        $this->assertEquals($expected[0], $range->getFrom());
        $this->assertEquals($expected[1], $range->getTo());
    }

    public function provideRanges()
    {
        yield [
            [10, 11],
            [10, 11],
        ];

        yield [
            [11, 10],
            [10, 11],
        ];

        yield [
            [10, 10],
            [10, 10],
        ];
    }
}

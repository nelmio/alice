<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition;

/**
 * @covers Nelmio\Alice\Definition\RangeName
 */
class RangeNameTest extends \PHPUnit_Framework_TestCase
{
    public function testReadAccessorsReturnPropertiesValues()
    {
        $name = 'user';
        $from = 10;
        $to = 100;
        
        $range = new RangeName($name, $from, $to);
        
        $this->assertEquals($name, $range->getName());
        $this->assertEquals($from, $range->getFrom());
        $this->assertEquals($to, $range->getTo());
        
        $range = new RangeName($name, $to, $from);

        $this->assertEquals($name, $range->getName());
        $this->assertEquals($from, $range->getFrom());
        $this->assertEquals($to, $range->getTo());
    }
}

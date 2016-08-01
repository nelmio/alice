<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Model;

/**
 * @covers Nelmio\Alice\Model\stdClass
 */
class stdClassTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAnStdClass()
    {
        $this->assertInstanceOf(\stdClass::class, new stdClass());
    }

    //TODO: https://github.com/nelmio/alice/issues/478
}

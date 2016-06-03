<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixture\Flag;

use Nelmio\Alice\Fixture\FlagInterface;

/**
 * @covers Nelmio\Alice\Fixture\Flag\TemplateFlag
 */
class TemplateFlagTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAFlag()
    {
        $this->assertTrue(is_a(TemplateFlag::class, FlagInterface::class, true));
    }

    public function testAccessors()
    {
        $flag = new TemplateFlag();
        $this->assertEquals('template', $flag->__toString());
    }
}

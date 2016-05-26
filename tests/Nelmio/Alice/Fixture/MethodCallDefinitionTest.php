<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixture;

/**
 * @covers Nelmio\Alice\Fixture\MethodCallDefinition
 */
class MethodCallDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testAccessors()
    {
        $caller = 'setValue';
        $arguments = [
            'hello',
            new \stdClass(),
        ];
        $requiresUnique = true;
        
        $definition = new MethodCallDefinition($caller, $arguments, $requiresUnique);
        
        $this->assertEquals($caller, $definition->getName());
        $this->assertSame($arguments, $definition->getArguments());
        $this->assertEquals($requiresUnique, $definition->requiresUnique());
    }
}

<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice;

use Nelmio\Alice\Fixture\Flag\TemplateFlag;
use Nelmio\Alice\Fixture\FlagBag;
use Nelmio\Alice\Fixture\MethodCallDefinition;
use Nelmio\Alice\Fixture\PropertyDefinition;
use Nelmio\Alice\Fixture\PropertyDefinitionBag;
use Nelmio\Alice\Fixture\SpecificationBag;

/**
 * @covers Nelmio\Alice\UnresolvedFixture
 */
class UnresolvedFixtureTest extends \PHPUnit_Framework_TestCase
{
    public function testAccessors()
    {
        $reference = 'user0';
        $className = 'Dummy';
        $specs = new SpecificationBag(
            new MethodCallDefinition('__construct', []),
            (new PropertyDefinitionBag())->with(new PropertyDefinition('username', null, true))
        );
        $flags = (new FlagBag('user'))->with(new TemplateFlag());
        
        $fixture = new UnresolvedFixture($reference, $className, $specs, $flags);

        $this->assertEquals($reference, $fixture->getReference());
        $this->assertEquals($className, $fixture->getClassName());
        $this->assertEquals($specs, $fixture->getSpecs());
        $this->assertEquals($flags, $fixture->getFlags());
    }

    public function testIsImmutable()
    {
        $reference = 'user0';
        $className = 'Dummy';
        $specs = new SpecificationBag(
            new MethodCallDefinition('__construct', []),
            (new PropertyDefinitionBag())->with(new PropertyDefinition('username', null, true))
        );
        $flags = (new FlagBag('user'))->with(new TemplateFlag());

        $fixture = new UnresolvedFixture($reference, $className, $specs, $flags);

        $this->assertNotSame($fixture->getSpecs(), $fixture->getSpecs());
        $this->assertNotSame($fixture->getFlags(), $fixture->getFlags());
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage Is not clonable.
     */
    public function testIsNotClonable()
    {
        $reference = 'user0';
        $className = 'Dummy';
        $specs = new SpecificationBag(
            new MethodCallDefinition('__construct', []),
            (new PropertyDefinitionBag())->with(new PropertyDefinition('username', null, true))
        );
        $flags = (new FlagBag('user'))->with(new TemplateFlag());

        $fixture = new UnresolvedFixture($reference, $className, $specs, $flags);
        clone $fixture;
    }
}

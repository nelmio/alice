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

namespace Nelmio\Alice\Definition\Fixture;

use Nelmio\Alice\Definition\MethodCall\DummyMethodCall;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\FixtureIdInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Throwable\Exception\NoValueForCurrentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Definition\Fixture\SimpleFixture
 */
class SimpleFixtureTest extends TestCase
{
    public function testIsAFixtureId()
    {
        $this->assertTrue(is_a(SimpleFixture::class, FixtureIdInterface::class, true));
    }

    public function testIsAFixture()
    {
        $this->assertTrue(is_a(SimpleFixture::class, FixtureInterface::class, true));
    }
    
    public function testReadAccessorsReturnPropertiesValues()
    {
        $reference = 'user0';
        $className = 'Nelmio\Alice\Entity\User';
        $specs = SpecificationBagFactory::create();

        $fixture = new SimpleFixture($reference, $className, $specs);

        $this->assertEquals($reference, $fixture->getId());
        $this->assertEquals($className, $fixture->getClassName());
        $this->assertEquals($specs, $fixture->getSpecs());
        try {
            $fixture->getValueForCurrent();
            $this->fail('Expected exception to be thrown.');
        } catch (NoValueForCurrentException $exception) {
            $this->assertEquals(
                'No value for \'<current()>\' found for the fixture "user0".',
                $exception->getMessage()
            );
        }

        $fixture = new SimpleFixture($reference, $className, $specs, 'alice');

        $this->assertEquals($reference, $fixture->getId());
        $this->assertEquals($className, $fixture->getClassName());
        $this->assertEquals($specs, $fixture->getSpecs());
        $this->assertEquals('alice', $fixture->getValueForCurrent());
    }

    public function testWithersReturnNewModifiedInstance()
    {
        $reference = 'user0';
        $className = 'Nelmio\Alice\Entity\User';
        $specs = SpecificationBagFactory::create();
        $newSpecs = SpecificationBagFactory::create(new DummyMethodCall('dummy'));

        $fixture = new SimpleFixture($reference, $className, $specs);
        $newFixture = $fixture->withSpecs($newSpecs);

        $this->assertInstanceOf(SimpleFixture::class, $newFixture);
        $this->assertNotSame($fixture, $newFixture);

        $this->assertEquals($specs, $fixture->getSpecs());
        $this->assertEquals($newSpecs, $newFixture->getSpecs());
    }
}

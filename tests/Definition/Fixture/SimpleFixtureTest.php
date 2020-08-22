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
    public function testIsAFixtureId(): void
    {
        static::assertTrue(is_a(SimpleFixture::class, FixtureIdInterface::class, true));
    }

    public function testIsAFixture(): void
    {
        static::assertTrue(is_a(SimpleFixture::class, FixtureInterface::class, true));
    }
    
    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $reference = 'user0';
        $className = 'Nelmio\Alice\Entity\User';
        $specs = SpecificationBagFactory::create();

        $fixture = new SimpleFixture($reference, $className, $specs);

        static::assertEquals($reference, $fixture->getId());
        static::assertEquals($className, $fixture->getClassName());
        static::assertEquals($specs, $fixture->getSpecs());
        try {
            $fixture->getValueForCurrent();
            static::fail('Expected exception to be thrown.');
        } catch (NoValueForCurrentException $exception) {
            static::assertEquals(
                'No value for \'<current()>\' found for the fixture "user0".',
                $exception->getMessage()
            );
        }

        $fixture = new SimpleFixture($reference, $className, $specs, 'alice');

        static::assertEquals($reference, $fixture->getId());
        static::assertEquals($className, $fixture->getClassName());
        static::assertEquals($specs, $fixture->getSpecs());
        static::assertEquals('alice', $fixture->getValueForCurrent());
    }

    public function testWithersReturnNewModifiedInstance(): void
    {
        $reference = 'user0';
        $className = 'Nelmio\Alice\Entity\User';
        $specs = SpecificationBagFactory::create();
        $newSpecs = SpecificationBagFactory::create(new DummyMethodCall('dummy'));

        $fixture = new SimpleFixture($reference, $className, $specs);
        $newFixture = $fixture->withSpecs($newSpecs);

        static::assertInstanceOf(SimpleFixture::class, $newFixture);
        static::assertNotSame($fixture, $newFixture);

        static::assertEquals($specs, $fixture->getSpecs());
        static::assertEquals($newSpecs, $newFixture->getSpecs());
    }
}

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
 * @internal
 */
final class SimpleFixtureTest extends TestCase
{
    public function testIsAFixtureId(): void
    {
        self::assertTrue(is_a(SimpleFixture::class, FixtureIdInterface::class, true));
    }

    public function testIsAFixture(): void
    {
        self::assertTrue(is_a(SimpleFixture::class, FixtureInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $reference = 'user0';
        $className = 'Nelmio\Alice\Entity\User';
        $specs = SpecificationBagFactory::create();

        $fixture = new SimpleFixture($reference, $className, $specs);

        self::assertEquals($reference, $fixture->getId());
        self::assertEquals($className, $fixture->getClassName());
        self::assertEquals($specs, $fixture->getSpecs());

        try {
            $fixture->getValueForCurrent();
            self::fail('Expected exception to be thrown.');
        } catch (NoValueForCurrentException $exception) {
            self::assertEquals(
                'No value for \'<current()>\' found for the fixture "user0".',
                $exception->getMessage(),
            );
        }

        $fixture = new SimpleFixture($reference, $className, $specs, 'alice');

        self::assertEquals($reference, $fixture->getId());
        self::assertEquals($className, $fixture->getClassName());
        self::assertEquals($specs, $fixture->getSpecs());
        self::assertEquals('alice', $fixture->getValueForCurrent());
    }

    public function testWithersReturnNewModifiedInstance(): void
    {
        $reference = 'user0';
        $className = 'Nelmio\Alice\Entity\User';
        $specs = SpecificationBagFactory::create();
        $newSpecs = SpecificationBagFactory::create(new DummyMethodCall('dummy'));

        $fixture = new SimpleFixture($reference, $className, $specs);
        $newFixture = $fixture->withSpecs($newSpecs);

        self::assertInstanceOf(SimpleFixture::class, $newFixture);
        self::assertNotSame($fixture, $newFixture);

        self::assertEquals($specs, $fixture->getSpecs());
        self::assertEquals($newSpecs, $newFixture->getSpecs());
    }
}

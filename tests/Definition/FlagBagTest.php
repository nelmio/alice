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

use Nelmio\Alice\Definition\Flag\AnotherDummyFlag;
use Nelmio\Alice\Definition\Flag\DummyFlag;
use Nelmio\Alice\Definition\Flag\ElementFlag;
use Nelmio\Alice\Definition\Flag\ElementWithToStringFlag;
use Nelmio\Alice\Definition\Flag\ExtendFlag;
use Nelmio\Alice\Definition\Flag\MutableFlag;
use Nelmio\Alice\Definition\Flag\OptionalFlag;
use Nelmio\Alice\Definition\Flag\TemplateFlag;
use Nelmio\Alice\Definition\Flag\UniqueFlag;
use Nelmio\Alice\Definition\ServiceReference\FixtureReference;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Nelmio\Alice\Definition\FlagBag
 */
class FlagBagTest extends TestCase
{
    public function testReadAccessorsReturnPropertiesValues()
    {
        $bag = new FlagBag('user0');

        $this->assertEquals('user0', $bag->getKey());
    }

    public function testIsImmutable()
    {
        $bag = (new FlagBag(''))->withFlag($flag = new MutableFlag('foo', new stdClass()));
        $flag->getObject()->foo = 'bar';

        $this->assertEquals(
            (new FlagBag(''))->withFlag($flag = new MutableFlag('foo', new stdClass())),
            $bag
        );

        $this->assertTrue(true, 'Nothing to do.');
    }

    public function testAddingAFlagCreatesANewModifiedInstance()
    {
        $flag = new MutableFlag('flag0', new stdClass());
        $bag1 = new FlagBag('user0');
        $bag2 = $bag1->withFlag($flag);

        $this->assertInstanceOf(FlagBag::class, $bag1);
        $this->assertNotSame($bag1, $bag2);

        $this->assertCount(0, $bag1);
        $this->assertCount(1, $bag2);


        // Mutate injected value
        $flag->setStringValue('flag1');
        $flag->getObject()->injected = true;

        // Mutate return value
        foreach ($bag1 as $flag) {
            /** @var MutableFlag $flag */
            $flag->setStringValue('flag2');
            $flag->getObject()->foo = 'bar';
        }

        $this->assertEquals(
            (new FlagBag('user0'))->withFlag(new MutableFlag('flag0', new stdClass())),
            $bag2
        );
    }

    public function testCanCreateANewFlagBagWithADifferentKey()
    {
        $bag1 = (new FlagBag('user0'))
            ->withFlag(
                new MutableFlag(
                    'flag0',
                    new stdClass()
                )
            )
        ;
        $bag2 = $bag1->withKey('user2');

        $this->assertInstanceOf(FlagBag::class, $bag1);
        $this->assertNotSame($bag1, $bag2);

        $this->assertCount(1, $bag1);
        $this->assertCount(1, $bag2);

        $this->assertEquals(
            (new FlagBag('user0'))->withFlag(new MutableFlag('flag0', new stdClass())),
            $bag1
        );
        $this->assertEquals(
            (new FlagBag('user2'))->withFlag(new MutableFlag('flag0', new stdClass())),
            $bag2
        );
    }

    public function testMergingTwoBagsCreatesANewModifiedInstance()
    {
        $bag1 = (new FlagBag('bag1'))->withFlag($flag1 = new MutableFlag('flag1', new stdClass()));
        $bag2 = (new FlagBag('bag2'))->withFlag($flag2 = new MutableFlag('flag2', new stdClass()));
        $mergedBag = $bag1->mergeWith($bag2);

        $this->assertInstanceOf(FlagBag::class, $mergedBag);

        $this->assertEquals(
            (new FlagBag('bag1'))->withFlag($flag1),
            $bag1
        );
        $this->assertEquals(
            (new FlagBag('bag2'))->withFlag(new MutableFlag('flag2', new stdClass())),
            $bag2
        );
        $this->assertEquals(
            (new FlagBag('bag1'))
                ->withFlag(new MutableFlag('flag1', new stdClass()))
                ->withFlag(new MutableFlag('flag2', new stdClass())),
            $mergedBag
        );
    }

    public function testCanMergeTwoBagsWithoutOverridingExistingValues()
    {
        $firstBag = (new FlagBag('first'))
            ->withFlag(new ElementWithToStringFlag('first_foo', 'foo'))
            ->withFlag(new ElementFlag('foz'))
        ;
        $secondBag = (new FlagBag('second'))
            ->withFlag(new ElementWithToStringFlag('second_foo', 'foo'))
            ->withFlag(new ElementFlag('baz'))
        ;

        $mergeFirstWithSecondWithOverriding = $firstBag->mergeWith($secondBag);
        $mergeFirstWithSecondWithoutOverriding = $firstBag->mergeWith($secondBag, false);
        $mergeSecondWithFirstWithOverriding = $secondBag->mergeWith($firstBag);
        $mergeSecondWithFirstWithoutOverriding = $secondBag->mergeWith($firstBag, false);

        $this->assertEquals(
            (new FlagBag('first'))
                ->withFlag(new ElementWithToStringFlag('first_foo', 'foo'))
                ->withFlag(new ElementFlag('foz')),
            $firstBag
        );
        $this->assertEquals(
            (new FlagBag('second'))
                ->withFlag(new ElementWithToStringFlag('second_foo', 'foo'))
                ->withFlag(new ElementFlag('baz')),
            $secondBag
        );

        $this->assertEquals(
            (new FlagBag('first'))
                ->withFlag(new ElementWithToStringFlag('second_foo', 'foo'))
                ->withFlag(new ElementFlag('foz'))
                ->withFlag(new ElementFlag('baz')),
            $mergeFirstWithSecondWithOverriding
        );
        $this->assertEquals(
            (new FlagBag('first'))
                ->withFlag(new ElementWithToStringFlag('first_foo', 'foo'))
                ->withFlag(new ElementFlag('foz'))
                ->withFlag(new ElementFlag('baz')),
            $mergeFirstWithSecondWithoutOverriding
        );

        $this->assertEquals(
            (new FlagBag('second'))
                ->withFlag(new ElementFlag('baz'))
                ->withFlag(new ElementWithToStringFlag('first_foo', 'foo'))
                ->withFlag(new ElementFlag('foz')),
            $mergeSecondWithFirstWithOverriding
        );
        $this->assertEquals(
            (new FlagBag('second'))
                ->withFlag(new ElementFlag('baz'))
                ->withFlag(new ElementWithToStringFlag('second_foo', 'foo'))
                ->withFlag(new ElementFlag('foz')),
            $mergeSecondWithFirstWithoutOverriding
        );
    }

    public function testIsCountable()
    {
        $flags = new FlagBag('user0');
        $this->assertCount(0, $flags);

        $flags = $flags->withFlag(new DummyFlag());
        $this->assertCount(1, $flags);
    }

    public function testDoesNotDuplicateFlags()
    {
        $flags = (new FlagBag('user0'))
            ->withFlag(new DummyFlag())
            ->withFlag(new DummyFlag())
            ->withFlag(new AnotherDummyFlag())
            ->withFlag(new AnotherDummyFlag())
        ;

        $this->assertCount(2, $flags);
    }

    public function testIsIterable()
    {
        $flag1 = new DummyFlag();
        $flag2 = new AnotherDummyFlag();

        $flags = (new FlagBag('user0'))
            ->withFlag($flag1)
            ->withFlag($flag2)
        ;

        $this->assertSameFlags(
            [
                $flag1,
                $flag2,
            ],
            $flags
        );
    }

    public function testCanAccumulateExtendFlags()
    {
        $extendFlag1 = new ExtendFlag(new FixtureReference('user_base'));
        $extendFlag2 = new ExtendFlag(new FixtureReference('user_with_owner'));

        $flags = (new FlagBag('user0'))
            ->withFlag($extendFlag1)
            ->withFlag($extendFlag2)
        ;

        $this->assertSameFlags(
            [
                $extendFlag1,
                $extendFlag2,
            ],
            $flags
        );
    }

    public function testCannotAccumulateOptionalFlags()
    {
        $optionalFlag1 = new OptionalFlag(20);
        $optionalFlag2 = new OptionalFlag(60);

        $flags = (new FlagBag('user0'))
            ->withFlag($optionalFlag1)
            ->withFlag($optionalFlag2)
        ;

        $this->assertSameFlags(
            [
                $optionalFlag2,
            ],
            $flags
        );
    }

    public function testCannotAccumulateTemplateFlags()
    {
        $templateFlag1 = new TemplateFlag();
        $templateFlag2 = new TemplateFlag();

        $flags = (new FlagBag('user0'))
            ->withFlag($templateFlag1)
            ->withFlag($templateFlag2)
        ;

        $this->assertSameFlags(
            [
                $templateFlag2,
            ],
            $flags
        );
    }

    public function testCannotAccumulateUniqueFlags()
    {
        $uniqueFlag1 = new UniqueFlag();
        $uniqueFlag2 = new UniqueFlag();

        $flags = (new FlagBag('user0'))
            ->withFlag($uniqueFlag1)
            ->withFlag($uniqueFlag2)
        ;

        $this->assertSameFlags(
            [
                $uniqueFlag2,
            ],
            $flags
        );
    }

    private function assertSameFlags(array $expected, FlagBag $actual)
    {
        $flags = [];
        foreach ($actual as $key => $value) {
            $flags[$key] = $value;
        }

        $this->assertEquals($expected, $flags);
    }
}

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

/**
 * @covers Nelmio\Alice\Definition\FlagBag
 */
class FlagBagTest extends \PHPUnit_Framework_TestCase
{
    public function testReadAccessorsReturnPropertiesValues()
    {
        $flags = new FlagBag('user0');

        $this->assertEquals('user0', $flags->getKey());
    }

    public function testIsImmutable()
    {
        $this->assertTrue(true, 'Nothing to do.');
    }

    public function testWithersReturnNewModifiedInstance()
    {
        $flag = new MutableFlag('flag0');
        $flags = new FlagBag('user0');
        $newFlags = $flags->withFlag($flag);

        $this->assertInstanceOf(FlagBag::class, $flags);
        $this->assertNotSame($flags, $newFlags);

        $this->assertCount(0, $flags);
        $this->assertCount(1, $newFlags);

        // Mutate injected value
        $flag->setValue('flag1');

        // Mutate return value
        foreach ($flags as $flag) {
            $flag->setvalue('flag2');
        }

        $this->assertEquals(
            $flags->withFlag(new MutableFlag('flag0')),
            $newFlags
        );

        $anotherBag = (new FlagBag('user2'))->withFlag(new MutableFlag('another_flag0'));
        $mergedBag = $newFlags->mergeWith($anotherBag);

        $this->assertInstanceOf(FlagBag::class, $mergedBag);
        $this->assertEquals('user0', $mergedBag->getKey(), 'Expected original key to be kept.');
        $this->assertCount(2, $mergedBag);

        // Mutate injected value
        $flag->setValue('another_flag1');

        // Mutate return value
        foreach ($flags as $flag) {
            $flag->setvalue('another_flag2');
        }

        $this->assertEquals(
            $newFlags->mergeWith($anotherBag),
            $mergedBag
        );

        $renamedBag = $anotherBag->withKey('dummy');
        $this->assertEquals(
            (new FlagBag('user2'))->withFlag(new MutableFlag('another_flag0')),
            $anotherBag
        );
        $this->assertEquals(
            (new FlagBag('dummy'))->withFlag(new MutableFlag('another_flag0')),
            $renamedBag
        );
    }

    public function testMergingTwoBagsIsImmutable()
    {
        $firstBag = (new FlagBag('first'))
            ->withFlag(new ElementWithToStringFlag('first_foo', 'foo'))
            ->withFlag(new ElementFlag('foz'))
        ;
        $secondBag = (new FlagBag('second'))
            ->withFlag(new ElementWithToStringFlag('second_foo', 'foo'))
            ->withFlag(new ElementFlag('baz'))
        ;

        $mergeFirstWithSecond = $firstBag->mergeWith($secondBag);
        $mergeSecondWithFirst = $secondBag->mergeWith($firstBag);

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
            $mergeFirstWithSecond
        );
        $this->assertEquals(
            (new FlagBag('second'))
                ->withFlag(new ElementFlag('baz'))
                ->withFlag(new ElementWithToStringFlag('first_foo', 'foo'))
                ->withFlag(new ElementFlag('foz')),
            $mergeSecondWithFirst
        );
    }

    public function testCanMergeTwoBagsWithoutOverriddingExistingValues()
    {
        $firstBag = (new FlagBag('first'))
            ->withFlag(new ElementWithToStringFlag('first_foo', 'foo'))
            ->withFlag(new ElementFlag('foz'))
        ;
        $secondBag = (new FlagBag('second'))
            ->withFlag(new ElementWithToStringFlag('second_foo', 'foo'))
            ->withFlag(new ElementFlag('baz'))
        ;

        $mergeFirstWithSecond = $firstBag->mergeWith($secondBag, false);
        $mergeSecondWithFirst = $secondBag->mergeWith($firstBag, false);

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
                ->withFlag(new ElementWithToStringFlag('first_foo', 'foo'))
                ->withFlag(new ElementFlag('foz'))
                ->withFlag(new ElementFlag('baz')),
            $mergeFirstWithSecond
        );
        $this->assertEquals(
            (new FlagBag('second'))
                ->withFlag(new ElementFlag('baz'))
                ->withFlag(new ElementWithToStringFlag('second_foo', 'foo'))
                ->withFlag(new ElementFlag('foz')),
            $mergeSecondWithFirst
        );
    }

    public function testIsCountable()
    {
        $flags = new FlagBag('user0');
        $this->assertEquals(0, count($flags));

        $flags = $flags->withFlag(new DummyFlag());
        $this->assertEquals(1, count($flags));
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

    public function testCanCumulateExtendFlags()
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

    public function testCannotCumulateOptionalFlags()
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

    public function testCannotCumulateTemplateFlags()
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

    public function testCannotCumulateUniqueFlags()
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

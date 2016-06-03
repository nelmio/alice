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

use Nelmio\Alice\Fixture\FlagBag;
use Nelmio\Alice\Fixture\PropertyDefinitionBag;
use Nelmio\Alice\Fixture\SpecificationBag;

/**
 * @covers Nelmio\Alice\UnresolvedFixtureBag
 */
class UnresolvedFixtureBagTest extends \PHPUnit_Framework_TestCase
{
    public function testImmutableMutators()
    {
        $bag = new UnresolvedFixtureBag();
        $newBag = $bag->with($this->createDummyFixture());
        
        $this->assertInstanceOf(UnresolvedFixtureBag::class, $newBag);
        $this->assertNotSame($bag, $newBag);
    }

    private function createDummyFixture()
    {
        return new UnresolvedFixture(
            'user0',
            'Dummy',
            new SpecificationBag(
                null,
                new PropertyDefinitionBag()
            ),
            new FlagBag('user0')
        );
    }
}

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

namespace Nelmio\Alice;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\ObjectSet
 */
class ObjectSetTest extends TestCase
{
    public function testReadAccessorsReturnPropertiesValues()
    {
        $parameters = new ParameterBag([
            'foo' => 'bar',
        ]);
        $objects = new ObjectBag([
            'dummy' => $std = new \stdClass(),
        ]);

        $set = new ObjectSet($parameters, $objects);

        $this->assertSame(
            [
                'foo' => 'bar',
            ],
            $set->getParameters()
        );
        $this->assertEquals(
            [
                'dummy' => $std
            ],
            $set->getObjects()
        );
        $this->assertCount(1, $set->getObjects());
    }
}

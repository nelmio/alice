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

namespace Nelmio\Alice\Definition\ServiceReference;

use Nelmio\Alice\Definition\ServiceReferenceInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Definition\ServiceReference\InstantiatedReference
 */
class InstantiatedReferenceTest extends TestCase
{
    public function testIsAReference()
    {
        $this->assertTrue(is_a(InstantiatedReference::class, ServiceReferenceInterface::class, true));
    }
    
    public function testReadAccessorsReturnPropertiesValues()
    {
        $reference = 'nelmio.alice.user_factory';
        $definition = new InstantiatedReference($reference);
        
        $this->assertEquals($reference, $definition->getId());
    }
}

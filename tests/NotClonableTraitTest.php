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

/**
 * @covers \Nelmio\Alice\NotClonableTrait
 */
class NotClonableTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage This class is not clonable. This could be the case because this has not been needed yet. Do not hesitate to reach out the maintainers to know if this can be made clonable.
     */
    public function testThrowsAnExceptionWhenTryingToCloneInstance()
    {
        clone new NotClonableDummy();
    }
}

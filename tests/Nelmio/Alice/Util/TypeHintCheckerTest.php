<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Util;

use Nelmio\Alice\TestPersister;
use PHPUnit\Framework\TestCase;

class TypeHintCheckerTest extends TestCase
{
    const DYNAMIC_CONSTRUCTOR_CLASS = 'Nelmio\Alice\support\models\DynamicConstructorClass';

    protected $typeHintChecker;

    public function setUp()
    {
        $persister = new TestPersister;
        $this->typeHintChecker = new TypeHintChecker;
        $this->typeHintChecker->setPersister($persister);
    }

    public function testAcceptVariableLengthArgumentList()
    {
        $class = self::DYNAMIC_CONSTRUCTOR_CLASS;
        $value = 'A';

        $result = $this->typeHintChecker->check($class, '__construct', $value, 0);
        $this->assertEquals($value, $result);
    }
}

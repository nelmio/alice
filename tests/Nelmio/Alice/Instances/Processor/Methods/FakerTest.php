<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Processor\Methods;

use Nelmio\Alice\FooProvider;

class FakerTest extends \PHPUnit_Framework_TestCase
{
    const USER = 'Nelmio\Alice\support\models\User';
    const MAGIC_USER = 'Nelmio\Alice\support\models\MagicUser';
    const GROUP = 'Nelmio\Alice\support\models\Group';
    const CONTACT = 'Nelmio\Alice\support\models\Contact';

    protected $persister;

    /**
     * @var \Nelmio\Alice\Fixtures\Loader
     */
    protected $loader;

    public function testAddProvider()
    {
        $faker = new Faker([]);
        $faker->addProvider(new FooProvider());
    }
}

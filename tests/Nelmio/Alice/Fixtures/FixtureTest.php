<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures;

use Nelmio\Alice\Fixtures\Fixture;

class FixtureTest extends \PHPUnit_Framework_TestCase
{
	const USER = 'Nelmio\Alice\support\models\User';
	const GROUP = 'Nelmio\Alice\support\models\Group';
	const CONTACT = 'Nelmio\Alice\support\models\Contact';

	public function testIsTemplateWithTemplateNameFlag()
	{
		$fixture = new Fixture(self::USER, 'user (template)', array(), null);

		$this->assertTrue($fixture->isTemplate());
	}
	
	public function testIsNotTemplateWithoutTemplateNameFlag()
	{
		$fixture = new Fixture(self::USER, 'user', array(), null);

		$this->assertFalse($fixture->isTemplate());
	}

	public function testIsNotTemplateWithExtendsNameFlag($value='')
	{
		$fixture = new Fixture(self::USER, 'user (extends user_template)', array(), null);

		$this->assertFalse($fixture->isTemplate());
	}

}

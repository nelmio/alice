<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Processor;

class ProcessableTest extends \PHPUnit_Framework_TestCase
{
    public function testValueMatchesWillReturnIfTheProcessablesValueMatchesAGivenRegex()
    {
        $processable = new Processable('<username()>');

        $this->assertTrue($processable->valueMatches('/username/'));
        $this->assertFalse($processable->valueMatches('/nomatch/'));
    }

    public function testGetMatchWillReturnTheMostRecentMatchIfItExists()
    {
        $processable = new Processable('<username()>');

        $this->assertNull($processable->getMatch('function'));

        $processable->valueMatches('/<(?<function>[a-z0-9_]*)\(\)>/');

        $this->assertEquals('username', $processable->getMatch('function'));
        $this->assertNull($processable->getMatch('nomatch'));
    }
}

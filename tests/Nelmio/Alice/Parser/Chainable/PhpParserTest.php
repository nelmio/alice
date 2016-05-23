<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Parser\Chainable;

use Nelmio\Alice\Parser\ChainableParserInterface;
use Nelmio\Alice\Parser\FileListProviderTrait;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\Parser\Chainable\PhpParser
 */
class PhpParserTest extends \PHPUnit_Framework_TestCase
{
    use FileListProviderTrait;
    
    private static $dir;

    /**
     * @var PhpParser
     */
    private $parser;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$dir = __DIR__.'/../File/Php';
    }

    public static function tearDownAfterClass()
    {
        self::$dir = null;

        parent::tearDownAfterClass();
    }

    public function setUp()
    {
        $this->parser = new PhpParser();
    }

    public function testIsAChainableParser()
    {
        $this->assertTrue(is_a(PhpParser::class, ChainableParserInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone $this->parser;
    }

    /**
     * @dataProvider providePhpList
     */
    public function testCanParsePhpFiles(string $file, array $expectedParsers)
    {
        $actual = $this->parser->canParse($file);
        $expected = (in_array(get_class($this->parser), $expectedParsers));
        
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider provideYamlList
     */
    public function testCannotParseYamlFiles(string $file)
    {
        $actual = $this->parser->canParse($file);

        $this->assertFalse($actual);
    }

    /**
     * @dataProvider provideUnsupportedList
     */
    public function testCannotParseUnsupportedFiles(string $file)
    {
        $actual = $this->parser->canParse($file);

        $this->assertFalse($actual);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Parser\InvalidArgumentException
     * @expectedExceptionMessage The file "/nowhere.php" could not be found.
     */
    public function testThrowExceptionIfFileDoesNotExist()
    {
        $this->parser->parse('/nowhere.php');
    }

    public function testParseRegularFile()
    {
        $actual = $this->parser->parse(self::$dir.'/basic.php');

        $this->assertSame(
            [
                'Nelmio\Alice\support\models\User' => [
                    'user0' => [
                        'fullname' => 'John Doe',
                    ],
                ],
            ],
            $actual
        );
    }

    public function testParseEmptyFile()
    {
        $actual = $this->parser->parse(self::$dir.'/empty.php');

        $this->assertSame([], $actual);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Parser\InvalidArgumentException
     * @expectedExceptionMessageRegExp /^The file ".+\/no_return\.php" must return a PHP array\.$/
     */
    public function testThrowExceptionIfNoArrayReturnedInParsedFile()
    {
        $this->parser->parse(self::$dir.'/no_return.php');
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Parser\InvalidArgumentException
     * @expectedExceptionMessageRegExp /^The file ".+\/wrong_return\.php" must return a PHP array\.$/
     */
    public function testThrowExceptionIfWrongValueReturnedInParsedFile()
    {
        $this->parser->parse(self::$dir.'/wrong_return.php');
    }
}

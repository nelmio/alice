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

namespace Nelmio\Alice\Parser\Chainable;

use Nelmio\Alice\Parser\ChainableParserInterface;
use Nelmio\Alice\Parser\FileListProviderTrait;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Parser\Chainable\PhpParser
 */
class PhpParserTest extends TestCase
{
    use FileListProviderTrait;

    private static $dir;

    /**
     * @var PhpParser
     */
    private $parser;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$dir = __DIR__.'/../../../fixtures/Parser/files/php';
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass()
    {
        self::$dir = null;

        parent::tearDownAfterClass();
    }

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->parser = new PhpParser();
    }

    public function testIsAChainableParser()
    {
        $this->assertTrue(is_a(PhpParser::class, ChainableParserInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(PhpParser::class))->isCloneable());
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
     * @dataProvider provideJsonList
     */
    public function testCannotParseJsonFiles(string $file)
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
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The file "/nowhere.php" could not be found.
     */
    public function testThrowsAnExceptionIfFileDoesNotExist()
    {
        $this->parser->parse('/nowhere.php');
    }

    public function testReturnsParsedFileContent()
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

    public function testParsingEmptyFileResultsInEmptySet()
    {
        $actual = $this->parser->parse(self::$dir.'/empty.php');

        $this->assertSame([], $actual);
    }

    /**
     * @expectedException \TypeError
     * @expectedExceptionMessageRegExp /^The file ".+\/no_return\.php" must return a PHP array\.$/
     */
    public function testThrowsAnExceptionIfNoArrayReturnedInParsedFile()
    {
        $this->parser->parse(self::$dir.'/no_return.php');
    }

    /**
     * @expectedException \TypeError
     * @expectedExceptionMessageRegExp /^The file ".+\/wrong_return\.php" must return a PHP array\.$/
     */
    public function testThrowsAnExceptionIfWrongValueReturnedInParsedFile()
    {
        $this->parser->parse(self::$dir.'/wrong_return.php');
    }
}

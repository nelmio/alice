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
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$dir = __DIR__.'/../../../fixtures/Parser/files/php';
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass(): void
    {
        self::$dir = null;

        parent::tearDownAfterClass();
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
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

    public function testThrowsAnExceptionIfFileDoesNotExist()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The file "/nowhere.php" could not be found.');

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

    public function testParseReturnsNamedParameters()
    {
        $actual = $this->parser->parse(self::$dir.'/named_parameters.php');

        $this->assertSame(
            [
                'Nelmio\Alice\DummyWithMethods' => [
                    'dummy_with_methods' => [
                        '__construct' => [
                            '$foo1' => 'foo1',
                            '$foo2' => 'foo2',
                        ],
                        '__calls' => [
                            [
                                'bar' => [
                                    '$bar1' => 'bar1',
                                    '$bar2' => 'bar2',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $actual
        );
    }

    public function testThrowsAnExceptionIfNoArrayReturnedInParsedFile()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessageMatches('/^The file ".+\/no_return\.php" must return a PHP array\.$/');

        $this->parser->parse(self::$dir.'/no_return.php');
    }

    public function testThrowsAnExceptionIfWrongValueReturnedInParsedFile()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessageMatches('/^The file ".+\/wrong_return\.php" must return a PHP array\.$/');

        $this->parser->parse(self::$dir.'/wrong_return.php');
    }
}

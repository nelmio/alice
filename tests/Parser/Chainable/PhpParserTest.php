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

use InvalidArgumentException;
use Nelmio\Alice\Parser\ChainableParserInterface;
use Nelmio\Alice\Parser\FileListProviderTrait;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use TypeError;

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
    
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$dir = __DIR__.'/../../../fixtures/Parser/files/php';
    }
    
    public static function tearDownAfterClass(): void
    {
        self::$dir = null;

        parent::tearDownAfterClass();
    }
    
    protected function setUp(): void
    {
        $this->parser = new PhpParser();
    }

    public function testIsAChainableParser(): void
    {
        static::assertTrue(is_a(PhpParser::class, ChainableParserInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        static::assertFalse((new ReflectionClass(PhpParser::class))->isCloneable());
    }

    /**
     * @dataProvider providePhpList
     */
    public function testCanParsePhpFiles(string $file, array $expectedParsers): void
    {
        $actual = $this->parser->canParse($file);
        $expected = (in_array(get_class($this->parser), $expectedParsers, true));

        static::assertEquals($expected, $actual);
    }

    /**
     * @dataProvider provideYamlList
     */
    public function testCannotParseYamlFiles(string $file): void
    {
        $actual = $this->parser->canParse($file);

        static::assertFalse($actual);
    }

    /**
     * @dataProvider provideJsonList
     */
    public function testCannotParseJsonFiles(string $file): void
    {
        $actual = $this->parser->canParse($file);

        static::assertFalse($actual);
    }

    /**
     * @dataProvider provideUnsupportedList
     */
    public function testCannotParseUnsupportedFiles(string $file): void
    {
        $actual = $this->parser->canParse($file);

        static::assertFalse($actual);
    }

    public function testThrowsAnExceptionIfFileDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The file "/nowhere.php" could not be found.');

        $this->parser->parse('/nowhere.php');
    }

    public function testReturnsParsedFileContent(): void
    {
        $actual = $this->parser->parse(self::$dir.'/basic.php');

        static::assertSame(
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

    public function testParsingEmptyFileResultsInEmptySet(): void
    {
        $actual = $this->parser->parse(self::$dir.'/empty.php');

        static::assertSame([], $actual);
    }

    public function testParseReturnsNamedParameters(): void
    {
        $actual = $this->parser->parse(self::$dir.'/named_parameters.php');

        static::assertSame(
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

    public function testThrowsAnExceptionIfNoArrayReturnedInParsedFile(): void
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessageMatches('/^The file ".+\/no_return\.php" must return a PHP array\.$/');

        $this->parser->parse(self::$dir.'/no_return.php');
    }

    public function testThrowsAnExceptionIfWrongValueReturnedInParsedFile(): void
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessageMatches('/^The file ".+\/wrong_return\.php" must return a PHP array\.$/');

        $this->parser->parse(self::$dir.'/wrong_return.php');
    }
}

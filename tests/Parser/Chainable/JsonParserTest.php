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
use Nelmio\Alice\Throwable\Exception\Parser\UnparsableFileException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Parser\Chainable\PhpParser
 */
class JsonParserTest extends TestCase
{
    use FileListProviderTrait;

    private static $dir;

    /**
     * @var JsonParser
     */
    private $parser;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$dir = __DIR__.'/../../../fixtures/Parser/files/json';
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
        $this->parser = new JsonParser();
    }

    public function testIsAChainableParser(): void
    {
        static::assertTrue(is_a(JsonParser::class, ChainableParserInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        static::assertFalse((new ReflectionClass(JsonParser::class))->isCloneable());
    }

    /**
     * @dataProvider provideJsonList
     */
    public function testCanParseJsonFiles(string $file, array $expectedParsers): void
    {
        $actual = $this->parser->canParse($file);
        $expected = (in_array(get_class($this->parser), $expectedParsers));

        static::assertEquals($expected, $actual);
    }

    /**
     * @dataProvider providePhpList
     */
    public function testCanNotParsePhpFiles(string $file): void
    {
        $actual = $this->parser->canParse($file);

        static::assertFalse($actual);
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
        $this->expectExceptionMessage('The file "/nowhere.json" could not be found.');

        $this->parser->parse('/nowhere.json');
    }

    public function testReturnsParsedFileContent(): void
    {
        $actual = $this->parser->parse(self::$dir.'/basic.json');

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
        $actual = $this->parser->parse(self::$dir.'/empty.json');

        static::assertSame([], $actual);
    }

    public function testParseReturnsNamedParameters(): void
    {
        $actual = $this->parser->parse(self::$dir.'/named_parameters.json');

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

    public function testThrowsAnExceptionIfInvalidJson(): void
    {
        $this->expectException(UnparsableFileException::class);
        $this->expectExceptionMessageMatches('/^The file ".+\/invalid\.json" does not contain valid JSON\.$/');

        $this->parser->parse(self::$dir.'/invalid.json');
    }
}

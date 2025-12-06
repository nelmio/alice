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
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(PhpParser::class)]
final class JsonParserTest extends TestCase
{
    use FileListProviderTrait;

    private static $dir;

    /**
     * @var JsonParser
     */
    private $parser;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$dir = __DIR__.'/../../../fixtures/Parser/files/json';
    }

    public static function tearDownAfterClass(): void
    {
        self::$dir = null;

        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        $this->parser = new JsonParser();
    }

    public function testIsAChainableParser(): void
    {
        self::assertTrue(is_a(JsonParser::class, ChainableParserInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(JsonParser::class))->isCloneable());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideJsonList')]
    public function testCanParseJsonFiles(string $file, array $expectedParsers): void
    {
        $actual = $this->parser->canParse($file);
        $expected = in_array($this->parser::class, $expectedParsers, true);

        self::assertEquals($expected, $actual);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('providePhpList')]
    public function testCanNotParsePhpFiles(string $file): void
    {
        $actual = $this->parser->canParse($file);

        self::assertFalse($actual);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideYamlList')]
    public function testCannotParseYamlFiles(string $file): void
    {
        $actual = $this->parser->canParse($file);

        self::assertFalse($actual);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideUnsupportedList')]
    public function testCannotParseUnsupportedFiles(string $file): void
    {
        $actual = $this->parser->canParse($file);

        self::assertFalse($actual);
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

        self::assertSame(
            [
                'Nelmio\Alice\support\models\User' => [
                    'user0' => [
                        'fullname' => 'John Doe',
                    ],
                ],
            ],
            $actual,
        );
    }

    public function testParsingEmptyFileResultsInEmptySet(): void
    {
        $actual = $this->parser->parse(self::$dir.'/empty.json');

        self::assertSame([], $actual);
    }

    public function testParseReturnsNamedParameters(): void
    {
        $actual = $this->parser->parse(self::$dir.'/named_parameters.json');

        self::assertSame(
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
            $actual,
        );
    }

    public function testThrowsAnExceptionIfInvalidJson(): void
    {
        $this->expectException(UnparsableFileException::class);
        $this->expectExceptionMessageMatches('/^The file ".+\/invalid\.json" does not contain valid JSON\.$/');

        $this->parser->parse(self::$dir.'/invalid.json');
    }
}

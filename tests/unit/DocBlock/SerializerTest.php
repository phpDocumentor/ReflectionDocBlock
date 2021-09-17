<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock;
use PHPUnit\Framework\TestCase;

use function str_replace;

use const PHP_EOL;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\Serializer
 * @covers ::<private>
 */
class SerializerTest extends TestCase
{
    /**
     * Call Mockery::close after each test.
     */
    public function tearDown(): void
    {
        m::close();
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     * @uses \phpDocumentor\Reflection\DocBlock
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Generic
     *
     * @covers ::__construct
     * @covers ::getDocComment
     */
    public function testReconstructsADocCommentFromADocBlock(): void
    {
        $expected = <<<'DOCCOMMENT'
/**
 * This is a summary
 *
 * This is a description
 *
 * @unknown-tag Test description for the unknown tag
 */
DOCCOMMENT;

        $fixture = new Serializer();

        $docBlock = new DocBlock(
            'This is a summary',
            new Description('This is a description'),
            [
                new DocBlock\Tags\Generic('unknown-tag', new Description('Test description for the unknown tag')),
            ]
        );

        $this->assertSameString($expected, $fixture->getDocComment($docBlock));
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     * @uses \phpDocumentor\Reflection\DocBlock
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Generic
     *
     * @covers ::__construct
     * @covers ::getDocComment
     */
    public function testAddPrefixToDocBlock(): void
    {
        $expected = <<<'DOCCOMMENT'
aa/**
aa * This is a summary
aa *
aa * This is a description
aa *
aa * @unknown-tag Test description for the unknown tag
aa */
DOCCOMMENT;

        $fixture = new Serializer(2, 'a');

        $docBlock = new DocBlock(
            'This is a summary',
            new Description('This is a description'),
            [
                new DocBlock\Tags\Generic('unknown-tag', new Description('Test description for the unknown tag')),
            ]
        );

        $this->assertSameString($expected, $fixture->getDocComment($docBlock));
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     * @uses \phpDocumentor\Reflection\DocBlock
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Generic
     *
     * @covers ::__construct
     * @covers ::getDocComment
     */
    public function testAddPrefixToDocBlockExceptFirstLine(): void
    {
        $expected = <<<'DOCCOMMENT'
/**
aa * This is a summary
aa *
aa * This is a description
aa *
aa * @unknown-tag Test description for the unknown tag
aa */
DOCCOMMENT;

        $fixture = new Serializer(2, 'a', false);

        $docBlock = new DocBlock(
            'This is a summary',
            new Description('This is a description'),
            [
                new DocBlock\Tags\Generic('unknown-tag', new Description('Test description for the unknown tag')),
            ]
        );

        $this->assertSameString($expected, $fixture->getDocComment($docBlock));
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     * @uses \phpDocumentor\Reflection\DocBlock
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Generic
     *
     * @covers ::__construct
     * @covers ::getDocComment
     */
    public function testWordwrapsAroundTheGivenAmountOfCharacters(): void
    {
        $expected = <<<'DOCCOMMENT'
/**
 * This is a
 * summary
 *
 * This is a
 * description
 *
 * @unknown-tag
 * Test
 * description
 * for the
 * unknown tag
 */
DOCCOMMENT;

        $fixture = new Serializer(0, '', true, 15);

        $docBlock = new DocBlock(
            'This is a summary',
            new Description('This is a description'),
            [
                new DocBlock\Tags\Generic('unknown-tag', new Description('Test description for the unknown tag')),
            ]
        );

        $this->assertSameString($expected, $fixture->getDocComment($docBlock));
    }

    /**
     * @covers ::__construct
     * @covers ::getDocComment
     */
    public function testNoExtraSpacesAfterTagRemoval(): void
    {
        $expected = <<<'DOCCOMMENT'
/**
 * @unknown-tag
 */
DOCCOMMENT;

        $expectedAfterRemove = <<<'DOCCOMMENT_AFTER_REMOVE'
/**
 */
DOCCOMMENT_AFTER_REMOVE;

        $fixture    = new Serializer(0, '', true, 15);
        $genericTag = new DocBlock\Tags\Generic('unknown-tag');

        $docBlock = new DocBlock('', null, [$genericTag]);
        $this->assertSameString($expected, $fixture->getDocComment($docBlock));

        $docBlock->removeTag($genericTag);
        $this->assertSameString($expectedAfterRemove, $fixture->getDocComment($docBlock));
    }

    public function assertSameString(string $expected, string $actual): void
    {
        $expected = str_replace(PHP_EOL, "\n", $expected);

        self::assertSame($expected, $actual);
    }
}

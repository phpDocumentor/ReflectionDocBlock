<?php declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2010-2018 Mike van Riel<mike@phpdoc.org>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock;
use PHPUnit\Framework\TestCase;

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
     * @covers ::__construct
     * @covers ::getDocComment
     * @uses phpDocumentor\Reflection\DocBlock\Description
     * @uses phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     * @uses phpDocumentor\Reflection\DocBlock
     * @uses phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses phpDocumentor\Reflection\DocBlock\Tags\Generic
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

        $this->assertSame($expected, $fixture->getDocComment($docBlock));
    }

    /**
     * @covers ::__construct
     * @covers ::getDocComment
     * @uses phpDocumentor\Reflection\DocBlock\Description
     * @uses phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     * @uses phpDocumentor\Reflection\DocBlock
     * @uses phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses phpDocumentor\Reflection\DocBlock\Tags\Generic
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

        $this->assertSame($expected, $fixture->getDocComment($docBlock));
    }

    /**
     * @covers ::__construct
     * @covers ::getDocComment
     * @uses phpDocumentor\Reflection\DocBlock\Description
     * @uses phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     * @uses phpDocumentor\Reflection\DocBlock
     * @uses phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses phpDocumentor\Reflection\DocBlock\Tags\Generic
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

        $this->assertSame($expected, $fixture->getDocComment($docBlock));
    }

    /**
     * @covers ::__construct
     * @covers ::getDocComment
     * @uses phpDocumentor\Reflection\DocBlock\Description
     * @uses phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     * @uses phpDocumentor\Reflection\DocBlock
     * @uses phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses phpDocumentor\Reflection\DocBlock\Tags\Generic
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

        $this->assertSame($expected, $fixture->getDocComment($docBlock));
    }

    /**
     * @covers ::__construct
     * @covers ::getDocComment
     */
    public function testNoExtraSpacesAfterTagRemoval()
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

        $fixture = new Serializer(0, '', true, 15);
        $genericTag = new DocBlock\Tags\Generic('unknown-tag');

        $docBlock = new DocBlock('', null, [$genericTag]);
        $this->assertSame($expected, $fixture->getDocComment($docBlock));

        $docBlock->removeTag($genericTag);
        $this->assertSame($expectedAfterRemove, $fixture->getDocComment($docBlock));
    }
}

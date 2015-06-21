<?php
/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2010-2015 Mike van Riel<mike@phpdoc.org>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\Serializer
 * @covers ::<private>
 */
class SerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getDocComment
     * @uses phpDocumentor\Reflection\DocBlock\Description
     * @uses phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     * @uses phpDocumentor\Reflection\DocBlock
     * @uses phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses phpDocumentor\Reflection\DocBlock\Tags\Generic
     */
    public function testReconstructsADocCommentFromADocBlock()
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
                new DocBlock\Tags\Generic('unknown-tag', new Description('Test description for the unknown tag'))
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
    public function testAddPrefixToDocBlock()
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
                new DocBlock\Tags\Generic('unknown-tag', new Description('Test description for the unknown tag'))
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
    public function testAddPrefixToDocBlockExceptFirstLine()
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
                new DocBlock\Tags\Generic('unknown-tag', new Description('Test description for the unknown tag'))
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
    public function testWordwrapsAroundTheGivenAmountOfCharacters()
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
                new DocBlock\Tags\Generic('unknown-tag', new Description('Test description for the unknown tag'))
            ]
        );

        $this->assertSame($expected, $fixture->getDocComment($docBlock));
    }

    /**
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testInitializationFailsIfIndentIsNotAnInteger()
    {
        new Serializer([]);
    }

    /**
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testInitializationFailsIfIndentStringIsNotAString()
    {
        new Serializer(0, []);
    }

    /**
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testInitializationFailsIfIndentFirstLineIsNotABoolean()
    {
        new Serializer(0, '', []);
    }

    /**
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testInitializationFailsIfLineLengthIsNotNullNorAnInteger()
    {
        new Serializer(0, '', false, []);
    }
}

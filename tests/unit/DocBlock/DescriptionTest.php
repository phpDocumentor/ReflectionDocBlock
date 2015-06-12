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
use phpDocumentor\Reflection\DocBlock\Tags\Deprecated;
use phpDocumentor\Reflection\DocBlock\Tags\Link;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\Description
 */
class DescriptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $examples
     * @dataProvider provideExampleDescriptions
     * @covers ::__construct
     * @covers ::render
     * @covers ::parse
     * @uses         phpDocumentor\Reflection\DocBlock\Description\PassthroughFormatter
     * @uses         phpDocumentor\Reflection\DocBlock\Tag
     * @uses         phpDocumentor\Reflection\DocBlock\Tags\Link
     */
    public function testParsesDescription($example)
    {
        $object = new Description($example);

        $this->assertSame($example, $object->render());
    }

    /**
     * @covers ::__construct
     * @covers ::render
     * @covers ::parse
     * @uses phpDocumentor\Reflection\DocBlock\Description\PassthroughFormatter
     */
    public function testInlineTagEscapingSequence()
    {
        $fixture = 'This is text for a description with literal {{@}link}.';
        $expected = 'This is text for a description with literal {@link}.';
        $object = new Description($fixture);
        $this->assertSame($expected, $object->render());
    }

    /**
     * @covers ::__construct
     * @covers ::render
     * @covers ::parse
     * @uses phpDocumentor\Reflection\DocBlock\Tag
     * @uses phpDocumentor\Reflection\DocBlock\Tags\Version
     * @uses phpDocumentor\Reflection\DocBlock\Tags\Link
     */
    public function testFormatterReceivesContentsAsTokens()
    {
        $fixture = <<<DESCRIPTION
This is a description with {a} {@link http://phpdoc.org/ link} or {@deprecated inline tag with {@link http://phpdoc.org
another link} in it}. Here is a solitary } and a { to test the regex. We can also escape at-signs like this
{@}example.com or {{@}link}.
DESCRIPTION;

        $expected = [
            'This is a description with {a} ',
            Link::create('@link http://phpdoc.org/ link'),
            ' or ',
            Deprecated::create("@deprecated inline tag with {@link http://phpdoc.org\nanother link} in it"),
            ". Here is a solitary } and a { to test the regex. We can also escape at-signs like this\n"
            . "@example.com or {@link}."
        ];

        $formatter = m::mock('phpDocumentor\Reflection\DocBlock\Description\Formatter');
        $formatter->shouldReceive('format')->with($expected)->andReturn($fixture);

        $object = new Description($fixture);
        $this->assertSame($fixture, $object->render($formatter));
    }

    /**
     * Provides a series of example strings that the parser should correctly interpret and return.
     *
     * @return string[][]
     */
    public function provideExampleDescriptions()
    {
        return [
            ['This is text for a description.'],
            ['This is text for a {@link http://phpdoc.org/ description} that uses an inline tag.'],
            ['{@link http://phpdoc.org/ This} is text for a description that starts with an inline tag.'],
            [
                'This is text for a description with {@internal inline tag with {@link http://phpdoc.org another '
                . 'inline tag} in it}.'
            ],
            ['This is text for a description containing { that is literal.'],
            ['This is text for a description containing {@internal inline tag that has { that is literal}.'],
            ['This is text for a description with {} that is not a tag.'],
            ['This is text for a description with {@internal inline tag with {} that is not an inline tag}.'],
            ['This is text for a description with an {@internal inline tag with literal {{@}link{} in it}.']
        ];
    }
}

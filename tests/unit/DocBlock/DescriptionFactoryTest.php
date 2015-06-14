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
/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\DescriptionFactory
 */
class DescriptionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     */
    public function testDescriptionCanRenderUsingABodyWithPlaceholdersAndTags()
    {
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

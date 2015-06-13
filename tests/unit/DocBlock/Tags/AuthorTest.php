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

namespace phpDocumentor\Reflection\DocBlock\Tags;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\Tags\Other;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\Tags\Author
 * @covers ::<private>
 */
class AuthorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getAuthorName
     */
    public function testHasTheAuthorName()
    {
        $expected = 'Mike van Riel';

        $fixture = new Author($expected, 'mike@phpdoc.org');

        $this->assertSame($expected, $fixture->getAuthorName());
    }

    /**
     * @covers ::__construct
     * @covers ::getAuthorName
     * @expectedException \InvalidArgumentException
     */
    public function testInitializationFailsIfAuthorNameIsNotAString()
    {
        new Author([], 'mike@phpdoc.org');
    }

    /**
     * @covers ::__construct
     * @covers ::getEmail
     */
    public function testHasTheAuthorMailAddress()
    {
        $expected = 'mike@phpdoc.org';

        $fixture = new Author('Mike van Riel', $expected);

        $this->assertSame($expected, $fixture->getEmail());
    }

    /**
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testInitializationFailsIfEmailIsNotAString()
    {
        new Author('Mike van Riel', []);
    }

    /**
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testInitializationFailsIfEmailIsNotValid()
    {
        new Author('Mike van Riel', 'mike');
    }

    /**
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testStringRepresentationIsReturned()
    {
        $fixture = new Author('Mike van Riel', 'mike@phpdoc.org');

        $this->assertSame('Mike van Riel<mike@phpdoc.org>', (string)$fixture);
    }

    /**
     * @covers ::create
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Author::<public>
     */
    public function testFactoryMethod()
    {
        $fixture = Author::create('Mike van Riel <mike@phpdoc.org>');

        $this->assertSame('Mike van Riel<mike@phpdoc.org>', (string)$fixture);
        $this->assertSame('Mike van Riel', $fixture->getAuthorName());
        $this->assertSame('mike@phpdoc.org', $fixture->getEmail());
    }

    /**
     * @covers ::create
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Author::<public>
     */
    public function testFactoryMethodReturnsNullIfItCouldNotReadBody()
    {
        $this->assertNull(Author::create('dfgr<'));
    }
}

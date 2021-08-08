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

namespace phpDocumentor\Reflection\DocBlock\Tags;

use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\Tags\Author
 * @covers ::<private>
 */
class AuthorTest extends TestCase
{
    /**
     * Call Mockery::close after each test.
     */
    public function tearDown(): void
    {
        m::close();
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Author::__construct
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getName
     */
    public function testIfCorrectTagNameIsReturned(): void
    {
        $fixture = new Author('Mike van Riel', 'mike@phpdoc.org');

        $this->assertSame('author', $fixture->getName());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Author::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Author::__toString
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::render
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getName
     */
    public function testIfTagCanBeRenderedUsingDefaultFormatter(): void
    {
        $fixture = new Author('Mike van Riel', 'mike@phpdoc.org');

        $this->assertSame('@author Mike van Riel <mike@phpdoc.org>', $fixture->render());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Author::__construct
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::render
     */
    public function testIfTagCanBeRenderedUsingSpecificFormatter(): void
    {
        $fixture = new Author('Mike van Riel', 'mike@phpdoc.org');

        $formatter = m::mock(Formatter::class);
        $formatter->shouldReceive('format')->with($fixture)->andReturn('Rendered output');

        $this->assertSame('Rendered output', $fixture->render($formatter));
    }

    /**
     * @covers ::__construct
     * @covers ::getAuthorName
     */
    public function testHasTheAuthorName(): void
    {
        $expected = 'Mike van Riel';

        $fixture = new Author($expected, 'mike@phpdoc.org');

        $this->assertSame($expected, $fixture->getAuthorName());
    }

    /**
     * @covers ::__construct
     * @covers ::getEmail
     */
    public function testHasTheAuthorMailAddress(): void
    {
        $expected = 'mike@phpdoc.org';

        $fixture = new Author('Mike van Riel', $expected);

        $this->assertSame($expected, $fixture->getEmail());
    }

    /**
     * @covers ::__construct
     */
    public function testInitializationFailsIfEmailIsNotValid(): void
    {
        $this->expectException('InvalidArgumentException');
        new Author('Mike van Riel', 'mike');
    }

    /**
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testStringRepresentationIsReturned(): void
    {
        $fixture = new Author('Mike van Riel', 'mike@phpdoc.org');

        $this->assertSame('Mike van Riel <mike@phpdoc.org>', (string) $fixture);

        // ---

        $fixture = new Author('0', 'zero@foo.bar');

        $this->assertSame('0 <zero@foo.bar>', (string) $fixture);
    }

    /**
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testStringRepresentationIsReturnedWithoutName(): void
    {
        $fixture = new Author('', 'mike@phpdoc.org');

        $this->assertSame('<mike@phpdoc.org>', (string) $fixture);
    }

    /**
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testStringRepresentationWithEmtpyEmail(): void
    {
        $fixture = new Author('Mike van Riel', '');

        $this->assertSame('Mike van Riel', (string) $fixture);
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Author::<public>
     *
     * @covers ::create
     * @dataProvider authorTagProvider
     */
    public function testFactoryMethod(string $input, string $output, string $name, string $email): void
    {
        $fixture = Author::create($input);

        $this->assertSame($output, (string) $fixture);
        $this->assertSame($name, $fixture->getAuthorName());
        $this->assertSame($email, $fixture->getEmail());
    }

    /** @return mixed[][] */
    public function authorTagProvider(): array
    {
        return [
            [
                'Mike van Riel <mike@phpdoc.org>',
                'Mike van Riel <mike@phpdoc.org>',
                'Mike van Riel',
                'mike@phpdoc.org',
            ],
            [
                'Mike van Riel < mike@phpdoc.org >',
                'Mike van Riel <mike@phpdoc.org>',
                'Mike van Riel',
                'mike@phpdoc.org',
            ],
            [
                'Mike van Riel',
                'Mike van Riel',
                'Mike van Riel',
                '',
            ],
        ];
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Author::<public>
     *
     * @covers ::create
     */
    public function testFactoryMethodReturnsNullIfItCouldNotReadBody(): void
    {
        $this->assertNull(Author::create('dfgr<'));
    }
}

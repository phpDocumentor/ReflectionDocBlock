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

namespace phpDocumentor\Reflection;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\Types\ContextFactory;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class ReconstitutingADocBlockTest extends TestCase
{
    /**
     * Call Mockery::close after each test.
     */
    public function tearDown(): void
    {
        m::close();
    }

    public function testReconstituteADocBlock(): void
    {
        /** @var string $docComment */
        $docComment;
        /** @var string $reconstitutedDocComment */
        $reconstitutedDocComment;

        include(__DIR__ . '/../../examples/03-reconstituting-a-docblock.php');

        $this->assertSame($docComment, $reconstitutedDocComment);
    }


    /**
     * Method
     *
     * Method which contains a modulo sign (%) in its description.
     *
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Generic
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     *
     * @covers ::__construct
     * @covers ::render
     * @covers ::__toString
     */
    public function testDescriptionCanContainPercent(): void
    {
        $factory = DocBlockFactory::createInstance();
        $contextFactory = new ContextFactory();

        $method = new \ReflectionMethod(self::class, 'testDescriptionCanContainPercent');

        $docblock = $factory->create(
            $method,
            $contextFactory->createFromReflector($method->getDeclaringClass())
        );

        $tag1 = new Generic('JoinColumn', new Description('(name="column_id", referencedColumnName="id")'));
        $tag2 = new Generic('JoinColumn', new Description('(name="column_id_2", referencedColumnName="id")'));

        $tags = [
            $tag1,
            $tag2,
        ];

        $fixture  = $docblock->getDescription();
        $expected = 'Method which contains a modulo sign (%) in its description.';
        $this->assertSame($expected, (string) $fixture);
    }
}

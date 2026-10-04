<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection;

use phpDocumentor\Reflection\DocBlock\Tags\Reference\Fqsen as FqsenRef;
use phpDocumentor\Reflection\DocBlock\Tags\See;
use phpDocumentor\Reflection\Types\Context;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class DocblockSeeTagResolvingTest extends TestCase
{
    public function testResolvesSeeFQSENOfInlineTags()
    {
        $context = new Context('\Project\Sub\Level', ['Issue2425B' => '\Project\Other\Level\Issue2425B', 'Aliased' => 'Project\Other\Level\Issue2425C']);
        $docblockString = <<<DOCBLOCK
/**
 * Class summary.
 *
 * A description containing an inline {@see Issue2425B::bar()} tag
 * to a class inside of the project referenced via a use statement.
 *
 * And here is another inline {@see Aliased::bar()} tag to a class
 * aliased via a use statement.
 */
DOCBLOCK;



        $factory  = DocBlockFactory::createInstance();
        $docblock = $factory->create($docblockString, $context);

        /** @var See $see1 */
        $see1 = $docblock->getDescription()->getTags()[0];

        $this->assertSame('\Project\Other\Level\Issue2425B::bar()', (string)$see1->getReference());
    }

    public function testResolvesSeeFQSENWithBookmarkFromTopLevelTag(): void
    {
        $docblockString = <<<'DOCBLOCK'
/**
 * Class summary.
 *
 * @see \Project\Other\Level\Issue3710::run()#42 Jumps inside the runner
 */
DOCBLOCK;

        $factory  = DocBlockFactory::createInstance();
        $docblock = $factory->create($docblockString);

        $seeTags = $docblock->getTagsByName('see');
        $this->assertCount(1, $seeTags);

        /** @var See $see */
        $see = $seeTags[0];
        $reference = $see->getReference();

        $this->assertInstanceOf(FqsenRef::class, $reference);
        $this->assertSame('42', $reference->getBookmark());
        $this->assertSame('\Project\Other\Level\Issue3710::run()', (string) $reference);
        $this->assertSame(
            '\Project\Other\Level\Issue3710::run()#42 Jumps inside the runner',
            (string) $see
        );
    }

    public function testResolvesSeeFQSENWithBookmarkFromInlineTag(): void
    {
        $context = new Context('\Project\Sub\Level', ['Runner' => '\Project\Other\Level\Issue3710']);
        $docblockString = <<<'DOCBLOCK'
/**
 * Class summary.
 *
 * A description containing {@see Runner::run()#42} with an inline bookmark.
 */
DOCBLOCK;

        $factory  = DocBlockFactory::createInstance();
        $docblock = $factory->create($docblockString, $context);

        $inlineTags = $docblock->getDescription()->getTags();
        $this->assertCount(1, $inlineTags);

        /** @var See $see */
        $see = $inlineTags[0];
        $reference = $see->getReference();

        $this->assertInstanceOf(FqsenRef::class, $reference);
        $this->assertSame('42', $reference->getBookmark());
        $this->assertSame('\Project\Other\Level\Issue3710::run()', (string) $reference);
    }
}

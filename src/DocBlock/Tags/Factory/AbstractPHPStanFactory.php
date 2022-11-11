<?php
/*
 * This file is part of phpDocumentor.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 *  @link      http://phpdoc.org
 *
 */

declare(strict_types=1);

namespace phpDocumentor\Reflection\DocBlock\Tags\Factory;

use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\InvalidTag;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

/**
 * Factory class creating tags using phpstan's parser
 *
 * This class uses {@see PHPStanFactory} implementations to create tags
 * from the ast of the phpstan docblock parser.
 *
 * @internal This class is not part of the BC promise of this library.
 */
class AbstractPHPStanFactory implements Factory
{
    private PhpDocParser $parser;
    private Lexer $lexer;
    /** @var PHPStanFactory[] */
    private array $factories;

    public function __construct(PHPStanFactory ...$factories)
    {
        $this->lexer = new Lexer();
        $constParser = new ConstExprParser();
        $this->parser = new PhpDocParser(new TypeParser($constParser), $constParser);
        $this->factories = $factories;
    }

    public function create(string $tagLine, ?TypeContext $context = null): Tag
    {
        $tokens = $this->lexer->tokenize($tagLine);
        $ast = $this->parser->parseTag(new TokenIterator($tokens));

        if ($context === null) {
            $context = new TypeContext('');
        }

        foreach ($this->factories as $factory) {
            if ($factory->supports($ast, $context)) {
                return $factory->create($ast, $context);
            }
        }

        return InvalidTag::create(
            $ast->name,
            (string) $ast->value
        );
    }
}

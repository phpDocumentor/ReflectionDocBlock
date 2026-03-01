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
use PHPStan\PhpDocParser\Parser\ParserException;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;
use RuntimeException;

use function property_exists;
use function rtrim;
use function str_replace;
use function trim;

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
        $config = new ParserConfig(['indexes' => true, 'lines' => true]);
        $this->lexer = new Lexer($config);
        $constParser = new ConstExprParser($config);
        $this->parser = new PhpDocParser(
            $config,
            new TypeParser($config, $constParser),
            $constParser
        );

        $this->factories = $factories;
    }

    public function create(string $tagLine, ?TypeContext $context = null): Tag
    {
        try {
            $tokens = $this->tokenizeLine($tagLine);
            $ast = $this->parser->parseTag($tokens);
            if (property_exists($ast->value, 'description') === true) {
                $ast->value->setAttribute(
                    'description',
                    rtrim($ast->value->description . $tokens->joinUntil(Lexer::TOKEN_END), "\n")
                );
            }
        } catch (ParserException $e) {
            return InvalidTag::create($tagLine, '')->withError($e);
        }

        if ($context === null) {
            $context = new TypeContext('');
        }

        try {
            foreach ($this->factories as $factory) {
                if ($factory->supports($ast, $context)) {
                    return $factory->create($ast, $context);
                }
            }
        } catch (RuntimeException $e) {
            return InvalidTag::create((string) $ast->value, 'method')->withError($e);
        } catch (ParserException $e) {
            return InvalidTag::create((string) $ast->value, $ast->name)->withError($e);
        }

        return InvalidTag::create(
            (string) $ast->value,
            $ast->name
        );
    }

    /**
     * Solve the issue with the lexer not tokenizing the line correctly
     *
     * This method is a workaround for the lexer that includes newline tokens with spaces. For
     * phpstan this isn't an issue, as it doesn't do a lot of things with the indentation of descriptions.
     * But for us is important to keep the indentation of the descriptions, so we need to fix the lexer output.
     */
    private function tokenizeLine(string $tagLine): TokenIterator
    {
        // Prefix continuation lines with "* ", which is consumed by the phpstan parser as TOKEN_PHPDOC_EOL.
        $tagLine = str_replace("\n", "\n* ", $tagLine);
        $tokens = $this->lexer->tokenize($tagLine . "\n");
        $fixed = [];
        foreach ($tokens as $token) {
            if ($token[Lexer::TYPE_OFFSET] === Lexer::TOKEN_PHPDOC_EOL) {
                // Strip "* " prefix (and other horizontal whitespace) again so it doesn't and up in the
                // description when we joinUntil() in create().
                $fixed[] = [
                    Lexer::VALUE_OFFSET => trim($token[Lexer::VALUE_OFFSET], "* \t"),
                    Lexer::TYPE_OFFSET => $token[Lexer::TYPE_OFFSET],
                    Lexer::LINE_OFFSET => $token[Lexer::LINE_OFFSET] ?? 0,
                ];

                continue;
            }

            $fixed[] = $token;
        }

        return new TokenIterator($fixed);
    }
}

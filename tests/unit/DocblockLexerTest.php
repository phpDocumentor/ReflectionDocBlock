<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection;

use PHPUnit\Framework\TestCase;

final class DocblockLexerTest extends TestCase
{
    /**
     * @dataProvider docblockProvider
     */
    public function testLexer(string $input, array $expectedTokens) : void
    {
        $lexer = new DocBlockLexer();

        $lexer->setInput($input);
        $lexer->moveNext();

        $tokens = [];
        while (true) {
            if (!$lexer->lookahead) {
                break;
            }

            $lexer->moveNext();
            $tokens[] = $lexer->token['type'];
        }

        $this->assertSame($expectedTokens, $tokens);
    }

    public function docblockProvider()
    {
        return [
            'empty single line docbloc' => [
                <<<DOCBLOCK
/** */
DOCBLOCK
                ,
                [
                    DocBlockLexer::T_START,
                    DocBlockLexer::T_END
                ]
            ],
            'empty docbloc' => [
                <<<DOCBLOCK
/**
 */
DOCBLOCK
                ,
                [
                    DocBlockLexer::T_START,
                    DocBlockLexer::T_CRLF,
                    DocBlockLexer::T_END
                ]
            ],
            'docblock with summary' => [
                <<<DOCBLOCK
/**
 * My docblock summary.
 */
DOCBLOCK
                ,
                [
                    DocBlockLexer::T_START,
                    DocBlockLexer::T_CRLF,
                    DocBlockLexer::T_LINE_START,
                    DocBlockLexer::T_WHITESPACE,
                    DocBlockLexer::T_STRING,
                    DocBlockLexer::T_WHITESPACE,
                    DocBlockLexer::T_STRING,
                    DocBlockLexer::T_WHITESPACE,
                    DocBlockLexer::T_STRING,
                    DocBlockLexer::T_DOT,
                    DocBlockLexer::T_CRLF,
                    DocBlockLexer::T_END
                ]
            ],
            'docblock simple tag' => [
                <<<DOCBLOCK
/**
 * @var
 */
DOCBLOCK
                ,
                [
                    DocBlockLexer::T_START,
                    DocBlockLexer::T_CRLF,
                    DocBlockLexer::T_LINE_START,
                    DocBlockLexer::T_WHITESPACE,
                    DocBlockLexer::T_AT,
                    DocBlockLexer::T_STRING,
                    DocBlockLexer::T_CRLF,
                    DocBlockLexer::T_END
                ]
            ],
            'docblock specialized tags' => [
        <<<DOCBLOCK
/**
 * @var:unittest
*/
DOCBLOCK
        ,
        [
            DocBlockLexer::T_START,
            DocBlockLexer::T_CRLF,
            DocBlockLexer::T_LINE_START,
            DocBlockLexer::T_WHITESPACE,
            DocBlockLexer::T_AT,
            DocBlockLexer::T_STRING,
            DocBlockLexer::T_COLON,
            DocBlockLexer::T_STRING,
            DocBlockLexer::T_CRLF,
            DocBlockLexer::T_END
        ]
    ]
        ];
    }
}

<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection;

use Doctrine\Common\Lexer\AbstractLexer;

final class DocBlockLexer extends AbstractLexer
{

    const T_START = 257;
    const T_END = 258;
    const T_LINE_START = 259;
    const T_AT = 260;
    const T_COLON = 261;


    const T_STRING = 262;
    const T_CHAR = 263;

    const T_CRLF = 264;
    const T_WHITESPACE = 265;

    const T_CLOSE_CURLY_BRACES = 102;
    const T_CLOSE_PARENTHESIS = 103;
    const T_COMMA = 104;
    const T_EQUALS = 105;
    const T_NAMESPACE_SEPARATOR = 107;
    const T_OPEN_CURLY_BRACES = 108;
    const T_OPEN_PARENTHESIS = 109;
    const T_MINUS = 113;
    const T_DOT = 114;



    /**
     * @var array
     */
    private $noCase = [
        '@' => self::T_AT,
        '.' => self::T_DOT,
        ',' => self::T_COMMA,
        '(' => self::T_OPEN_PARENTHESIS,
        ')' => self::T_CLOSE_PARENTHESIS,
        '{' => self::T_OPEN_CURLY_BRACES,
        '}' => self::T_CLOSE_CURLY_BRACES,
        '=' => self::T_EQUALS,
        ':' => self::T_COLON,
        '-' => self::T_MINUS,
        '\\' => self::T_NAMESPACE_SEPARATOR,
    ];

    protected function getCatchablePatterns()
    {
        return [
            '\\/\\*\\*',
            '[[:blank:]]*\\*\\/',
            '[[:blank:]]*\\*',
            '[a-z][a-z]*',
            '\n',
            '[[:blank:]]+'
        ];
    }

    protected function getNonCatchablePatterns()
    {
        return [
            '(.)',
        ];
    }

    protected function getType(&$value)
    {
        if (isset($this->noCase[$value])) {
            return $this->noCase[$value];
        }

        switch ($value) {
            case '/**':
                return self::T_START;
            case trim($value) === '*/':
                return self::T_END;
            case '@':
                return self::T_AT;
            case trim($value) === '*':
                return self::T_LINE_START;
            case "\n":
                return self::T_CRLF;
            case strlen($value) > 1:
                return self::T_STRING;
            case preg_match('/^[[:blank:]]+$/', $value) !== false:
                return self::T_WHITESPACE;
            default:
                return self::T_CHAR;
        }
    }
}

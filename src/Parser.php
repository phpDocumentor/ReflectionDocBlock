<?php
/**
 * This file is part of phpDocumentor.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 *  @link      http://phpdoc.org
 *
 */


namespace phpDocumentor\Reflection;


/* This is an automatically GENERATED file, which should not be manually edited.
 */
class Parser extends BaseParser
{
    protected $tokenToSymbolMapSize = 266;
    protected $actionTableSize      = 23;
    protected $gotoTableSize        = 4;

    protected $invalidSymbol       = 11;
    protected $errorSymbol         = 1;
    protected $defaultAction       = -32766;
    protected $unexpectedTokenRule = 32767;

    protected $numNonLeafStates    = 15;

    protected $YY2TBLSTATE = 8;
    protected $YYNLSTATES  = 15;

    protected $symbolToName = array(
        "EOF",
        "error",
        "T_START",
        "T_END",
        "T_LINE_START",
        "T_AT",
        "T_COLON",
        "T_STRING",
        "T_CHAR",
        "T_CRLF",
        "T_WHITESPACE"
    );

    protected $tokenToSymbol = array(
            0,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,   11,   11,   11,   11,
           11,   11,   11,   11,   11,   11,    1,    2,    3,    4,
            5,    6,    7,    8,    9,   10
    );

    protected $action = array(
           24,   25,   26,    0,   27,   16,    1,    8,   12,   17,
           18,    0,    0,   11,    0,   14,    0,   13,   22,    0,
            0,   29,   30
    );

    protected $actionCheck = array(
            6,    7,    8,    0,   10,    3,    4,    2,    4,    3,
            3,   -1,   -1,    5,   -1,    6,   -1,    7,    7,   -1,
           -1,    9,    9
    );

    protected $actionBase = array(
            5,    8,   -6,   -6,    2,    4,    6,    7,   12,   12,
            3,   10,    8,    9,   11,    0,   -6,    0,    0,   13,
           12,   13,   13
    );

    protected $actionDefault = array(
        32767,32767,    8,   13,32767,   16,32767,32767,   16,   16,
        32767,32767,32767,    6,32767
    );

    protected $goto = array(
            4,    6,    2,   20
    );

    protected $gotoCheck = array(
            2,    2,    6,    5
    );

    protected $gotoBase = array(
            0,    0,   -8,    0,    0,   -2,    1
    );

    protected $gotoDefault = array(
        -32768,   10,    7,    9,    5,   19,    3
    );

    protected $ruleToNonTerminal = array(
            0,    1,    1,    1,    4,    4,    5,    5,    3,    6,
            6,    6,    6,    6,    2,    2,    2
    );

    protected $ruleToLength = array(
            1,    3,    5,    5,    1,    2,    3,    5,    2,    1,
            1,    1,    1,    2,    1,    2,    0
    );

    protected function initReduceCallbacks() {
        $this->reduceCallbacks = [
            0 => function ($stackPos) {
            $this->semValue = $this->semStack[$stackPos];
        },
            1 => function ($stackPos) {
             $this->semValue = new AST\Docblock(); 
            },
            2 => function ($stackPos) {
             $this->semValue = new AST\Docblock($this->semStack[$stackPos-(5-3)]); 
            },
            3 => function ($stackPos) {
             $this->semValue = new AST\Docblock(null, ...$this->semStack[$stackPos-(5-3)]); 
            },
            4 => function ($stackPos) {
             $this->semValue = [$this->semStack[$stackPos-(1-1)]]; 
            },
            5 => function ($stackPos) {
             $tags = $this->semStack[$stackPos-(2-1)]; $tags[] = $this->semStack[$stackPos-(2-2)]; $this->semValue = $tags; 
            },
            6 => function ($stackPos) {
             $this->semValue = new AST\Tag($this->semStack[$stackPos-(3-3)]); 
            },
            7 => function ($stackPos) {
             $this->semValue = new AST\Tag($this->semStack[$stackPos-(5-3)], $this->semStack[$stackPos-(5-5)]); 
            },
            8 => function ($stackPos) {
             $this->semValue = $this->semStack[$stackPos-(2-2)]; 
            },
            9 => function ($stackPos) {
            $this->semValue = $this->semStack[$stackPos];
        },
            10 => function ($stackPos) {
            $this->semValue = $this->semStack[$stackPos];
        },
            11 => function ($stackPos) {
            $this->semValue = $this->semStack[$stackPos];
        },
            12 => function ($stackPos) {
            $this->semValue = $this->semStack[$stackPos];
        },
            13 => function ($stackPos) {
             $this->semValue = $this->semStack[$stackPos-(2-1)] . $this->semStack[$stackPos-(2-2)]; 
            },
            14 => function ($stackPos) {
            $this->semValue = $this->semStack[$stackPos];
        },
            15 => function ($stackPos) {
            $this->semValue = $this->semStack[$stackPos];
        },
            16 => function ($stackPos) {
            $this->semValue = $this->semStack[$stackPos];
        },
        ];
    }
}

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

$meta #
#semval($) $this->semValue
#semval($,%t) $this->semValue
#semval(%n) $stackPos-(%l-%n)
#semval(%n,%t) $stackPos-(%l-%n)

namespace phpDocumentor\Reflection;

#include;

/* This is an automatically GENERATED file, which should not be manually edited.
 */
class Parser extends BaseParser
{
    protected $tokenToSymbolMapSize = #(YYMAXLEX);
    protected $actionTableSize      = #(YYLAST);
    protected $gotoTableSize        = #(YYGLAST);

    protected $invalidSymbol       = #(YYBADCH);
    protected $errorSymbol         = #(YYINTERRTOK);
    protected $defaultAction       = #(YYDEFAULT);
    protected $unexpectedTokenRule = #(YYUNEXPECTED);

    protected $numNonLeafStates    = #(YYNLSTATES);

    protected $YY2TBLSTATE = #(YY2TBLSTATE);
    protected $YYNLSTATES  = #(YYNLSTATES);

    protected $symbolToName = array(
        #listvar terminals
    );

    protected $tokenToSymbol = array(
        #listvar yytranslate
    );

    protected $action = array(
        #listvar yyaction
    );

    protected $actionCheck = array(
        #listvar yycheck
    );

    protected $actionBase = array(
        #listvar yybase
    );

    protected $actionDefault = array(
        #listvar yydefault
    );

    protected $goto = array(
        #listvar yygoto
    );

    protected $gotoCheck = array(
        #listvar yygcheck
    );

    protected $gotoBase = array(
        #listvar yygbase
    );

    protected $gotoDefault = array(
        #listvar yygdefault
    );

    protected $ruleToNonTerminal = array(
        #listvar yylhs
    );

    protected $ruleToLength = array(
        #listvar yylen
    );
#if -t

    protected $productions = array(
        #production-strings;
    );
#endif

    protected function initReduceCallbacks() {
        $this->reduceCallbacks = [
#reduce
            %n => function ($stackPos) {
            %b
            },
#noact
            %n => function ($stackPos) {
            $this->semValue = $this->semStack[$stackPos];
        },
#endreduce
        ];
    }
}
#tailcode;

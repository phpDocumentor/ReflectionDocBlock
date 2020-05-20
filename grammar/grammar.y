%start DOCBLOCK

%token T_START
%token T_END
%token T_LINE_START
%token T_AT T_COLON
%token T_STRING

%token T_CHAR T_CRLF T_WHITESPACE
%%

DOCBLOCK:
      T_START ALL_WHITESPACE T_END { $$ = new AST\Docblock(); }
    | T_START ALL_WHITESPACE SUMMARY ALL_WHITESPACE T_END { $$ = new AST\Docblock($3); }
    | T_START ALL_WHITESPACE TAGS ALL_WHITESPACE T_END { $$ = new AST\Docblock(null, ...$3); }

TAGS:
      TAG { $$ = [$1]; }
    | TAGS TAG { $tags = $1; $tags[] = $2; $$ = $tags; }
;

/* Should we have some T_WHITESPACE here? */
TAG:
      T_LINE_START T_AT T_STRING { $$ = new AST\Tag($3); }
    | T_LINE_START T_AT T_STRING T_COLON T_STRING { $$ = new AST\Tag($3, $5); }

SUMMARY: T_LINE_START PRINTABLE { $$ = $2; }

PRINTABLE: T_COLON | T_STRING | T_CHAR | T_WHITESPACE | PRINTABLE PRINTABLE { $$ = $1 . $2; }
ALL_WHITESPACE: T_CRLF | ALL_WHITESPACE T_CRLF | /* empty */

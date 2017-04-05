//
// LEXEMES
//
%token true true
%token false false
%token null null
%token escape_token \\
%token string .+


//
// RULES
//
value:
    string()

string:
    ::escape_token:: <string> | <escape_token> | <string>

//
//
//
//
//
//
//%skip   space          \s
//// Scalars.
//%token  true           true
//%token  false          false
//%token  null           null
//// Strings.
//%token  quote_         <{        -> string
//%token  string:string  [^"]+
//%token  string:_quote  }>        -> default
//// Objects.
//%token  brace_         {
//%token _brace          }
//// Arrays.
//%token  bracket_       \[
//%token _bracket        \]
//// Rest.
//%token  colon          :
//%token  comma          ,
//%token  number         \d+
//
//value:
//    <true> | <false> | <null> | string() | object() | array() | number()
//
//string:
//    ::quote_:: <string> ::_quote::
//
//number:
//    <number>
//
//#object:
//    ::brace_:: pair() ( ::comma:: pair() )* ::_brace::
//
//#pair:
//    string() ::colon:: value()
//
//#array:
//    ::bracket_:: value() ( ::comma:: value() )* ::_bracket::
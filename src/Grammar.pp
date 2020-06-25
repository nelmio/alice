//
// This file is part of the Alice package.
//
// (c) Nelmio <hello@nelm.io>
//
// For the full copyright and license information, please view the LICENSE
// file that was distributed with this source code.
//

// All whitespaces matter, except the trailing ones.
%skip  trailing_whitespaces    \s+$

// An opening chevron must not be escaped by a backslash.
// Matching an opening chevron changes the namespace from `default` to
// `parameter`.
%token opening_chevron    (?<!\\)<    -> parameter

// All whitespaces.
%skip parameter:whitespaces    \s+

// A closing chevron.
// Matching an closing chevron changes the namespace from `parameter`
// to `default`.
%token parameter:closing_chevron    >    -> __shift__

// A variable opening.
%token parameter:opening_variable    {    -> variable

// All whitespaces.
%skip variable:whitespaces    \s+

// An expansion list separator.
%token variable:comma    ,

// A range separator
%token variable:range    \.\.

// A range bound.
%token variable:number    [+-]?[0-9]+

// A variable name can be anything except `}`.
%token variable:name    [_\w][_\w\d]*

// A variable closing.
%token variable:closing_variable    }    -> __shift__

// Opening parenthesis.
%token parameter:opening_parenthesis    \(

// Closing parenthesis.
%token parameter:closing_parenthesis    \)

// Constant string.
%token parameter:string    ("|')(.*?)(?<!\\)\1

// A comma used to separate items in a list.
%token parameter:comma    ,

// A variable or a function name.
%token parameter:name    [_\w][_\w\d]*


// A reference is prefixed by an `@`.
%token at    @    -> reference

// A star is a glob operator.
%token reference:star    \*    -> __shift__

// A left curly bracket introduces an expansion.
%token reference:opening_expansion    {    -> expansion

// All whitespaces.
%skip expansion:whitespaces    \s+

// A number can be signed or not.
%token expansion:number    [-+]?[0-9]+

// A range is represented by two dots.
%token expansion:range    \.\.

// A comma is the name separator.
%token expansion:comma    ,

// A reference expansion name is just like a reference constant name.
%token expansion:name    [_\w][_\w\d]*

// A right curly bracket closes an expansion.
%token expansion:closing_expansion    }    -> __shift__ * 2

// A reference name is dynamic if some parts of its name are known at runtime.
%token reference:dynamic_name    [_\w][_\w\d]*(?=[\*\{])

// A constant reference name is not a dynamic reference name.
%token reference:constant_name    [_\w][_\w\d]*    ->    __shift__

// Anything is a little bit tricky because it must stop on an
// unescaped opening chevron. Thus:
//      .+
// is wrong because it is greedy. It must be lazy, so:
//     .+?
//
// However, it does not take into account the opening chevron. Thus:
//     .+?(?=<)
//
// This is valid but it does not take into account that the opening
// chevron must be unescaped. And now it's funny. Thus:
//     (\\<|.)+?(?=<)
//
// However, this works if and only if an unescaped opening chevron
// exists on the right. So the right assertion must be `<` or `$`,
// thus:
//     (\\<|.)+?(?=(<|$))
//
// The final result contains non-capturing groups for memory concerns.
//
// Repeat this reasoning for each Alice opening symbol (like `@`).
%token anything    (?:\\<|@@|.)+?(?=(?:<|@|$))

#root:
    ( anything()? ( parameter() | reference() ) )* anything()?

#parameter:
    ::opening_chevron:: ( variable() | identity() | function() ) ::closing_chevron::

#variable:
    ::opening_variable:: <name> ::closing_variable::

variable_expansion_list:
    ::opening_variable:: expansion_list() ::closing_variable::

#expansion_list:
    <name> ( ::comma:: <name> )*

variable_range:
    ::opening_variable:: range() ::closing_variable::

#range:
    <number> ::range:: <number>

#identity:
    ::opening_parenthesis:: <name> ::closing_parenthesis::

#function:
    <name> ::opening_parenthesis:: function_arguments()? ::closing_parenthesis::

function_arguments:
    function_argument() ( ::comma:: function_argument() )* #arguments

function_argument:
    <string>

reference:
    ::at::
    (
        <constant_name> #constant_reference
      | <dynamic_name>
        (
            ::star:: #glob_reference
          | ::opening_expansion::
            ( reference_range() | reference_list() )
            ::closing_expansion:: #expansion_reference
        )
    )

reference_range:
    <number> ::range:: <number> #range

reference_list:
    <name> ( ::comma:: <name> )* #list

#anything:
    <anything>

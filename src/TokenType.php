<?php

declare(strict_types=1);

namespace Neumb\JsonScanner;

enum TokenType
{
    case LeftBrace;
    case RightBrace;
    case String;
    case Colon;
    case Comma;
    case Number;
    case LeftBracket;
    case RightBracket;
    case True;
    case False;
    case Null;
}

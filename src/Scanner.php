<?php

declare(strict_types=1);

namespace Neumb\JsonScanner;

use Generator;
use Neumb\JsonScanner\ScanException;

final class Scanner
{
    private const KEYWORDS = [
        "true" => TokenType::True,
        "false" => TokenType::False,
        "null" => TokenType::Null,
    ];

    private array $chars;
    private int $len;
    private int $i = 0;

    public function __construct(
        private string $data,
    ) {
        $this->chars = str_split($data);
        $this->len = count($this->chars);
    }

    public static function from(string $data): Scanner
    {
        return new Scanner($data);
    }

    public function scan(): Generator
    {
        $this->i = 0;

        while($this->i < $this->len) {
            $char = $this->peek();
            switch ($char) {
                case "\t":
                case "\r":
                case "\n":
                case "\f":
                case " ": {
                    // skip
                    $this->advance();
                } break;
                case "{": {
                    yield new Token(TokenType::LeftBrace, $char, $this->i);
                    $this->advance();
                } break;
                case "}": {
                    yield new Token(TokenType::RightBrace, $char, $this->i);
                    $this->advance();
                } break;
                case "[": {
                    yield new Token(TokenType::LeftBracket, $char, $this->i);
                    $this->advance();
                } break;
                case "]": {
                    yield new Token(TokenType::RightBracket, $char, $this->i);
                    $this->advance();
                } break;
                case "\"": {
                    yield $this->str();
                } break;
                case ":": {
                    yield new Token(TokenType::Colon, $char, $this->i);
                    $this->advance();
                } break;
                case ",": {
                    yield new Token(TokenType::Comma, $char, $this->i);
                    $this->advance();
                } break;
                default: {
                    if (ctype_digit($char) || in_array($char, ["-", "+"], true)) {
                        yield $this->num();
                    } else {
                        yield $this->keyword();
                    }
                } break;
            }
        }
    }

    private function keyword(): Token
    {
        $pos = $this->i;
        $kw = "";

        if (! ctype_alnum($c = $this->peek())) {
            throw new ScanException("Unexpected char '{$char}' at pos {$this->i}");
        }

        while ($this->i < $this->len) {
            if (ctype_alnum($this->peek())) {
                $kw .= $this->advance();
            } else {
                break;
            }
        }

        if (! isset(self::KEYWORDS[$kw])) {
            throw new ScanException("Unexpected keyword '{$kw}' at pos {$this->i}");
        }

        return new Token(self::KEYWORDS[$kw], $kw, $pos);
    }

    private function num(): Token
    {
        $pos = $this->i;

        $numSign = "";
        $num = "";
        $frac = "";
        $expoSign = "";
        $expoLetter = "";
        $expo = "";

        if (in_array($this->peek(), ["-", "+"], true)) {
            $numSign = $this->advance();
        }

        /**
         * 0 - integer
         * 1 - fraction
         * 2 - exponent
         */
        $state = 0;

        while ($this->i < $this->len) {
            $c = $this->peek();

            switch ($state) {
                case 0:
                case 1: {
                    if (ctype_digit($c)) {
                        $num .= $this->advance();
                    } elseif ($state === 0 && $c === ".") {
                        $this->advance();
                        $state = 1;
                    } elseif ($c === "e" || $c === "E") {
                        $expoLetter = $this->advance();
                        $state = 2;
                    } else {
                        break 2;
                    }
                } break;
                case 2: {
                    if (ctype_digit($c)) {
                        $expo .= $this->advance();
                    } elseif ($c === "-" || $c === "+") {
                        $expoSign = $this->advance();
                    } else {
                        break 2;
                    }
                } break;
                default: {
                    throw new ScanException("Unreachable");
                } break;
            }
        }

        $lex = $numSign.$num.$expoLetter.$expoSign.$expo;

        if (strlen($frac) > 0) {
            $lex .= ".$frac";
        }

        return new Token(TokenType::Number, $lex, $pos);
    }

    private function str(): Token
    {
        $this->consume("\"");
        $pos = $this->i + 1;
        $str = "";

        while ($this->i < $this->len && $this->peek() !== "\"") {
            $c = $this->advance();
            if ($c === "\\") {
                switch ($c = $this->advance()) {
                    case "u": {
                        $pos = $this->i;
                        $hex = join([
                            $this->advance(),
                            $this->advance(),
                            $this->advance(),
                            $this->advance(),
                        ]);

                        if (!ctype_xdigit($hex)) {
                            throw new ScanException("Invalid unicode sequence at pos {$pos}");
                        }

                        $str .= mb_convert_encoding(PACK('H*', $hex), 'UTF-8', 'UTF-16BE');
                    } break;
                    case "\"":
                    case "\\":
                    case "/": {
                        $str .= $c;
                    } break;
                    case "b": {
                        $str .= "\b";
                    } break;
                    case "f": {
                        $str .= "\f";
                    } break;
                    case "n": {
                        $str .= "\n";
                    } break;
                    case "t": {
                        $str .= "\t";
                    } break;
                    case "r": {
                        $str .= "\r";
                    } break;
                    default: {
                        throw new ScanException("Invalid escape at pos {$this->i}");
                    } break;
                }
            } else {
                $str .= $c;
            }
        }

        // consume the closing quote
        $this->consume("\"");

        return new Token(TokenType::String, $str, $pos);
    }

    private function previous(): string
    {
        return $this->chars[$this->i - 1];
    }

    private function peek(): string
    {
        return $this->chars[$this->i];
    }

    private function consume(string $expected): void
    {
        $got = $this->peek();
        if ($got !== $expected) {
            throw new ScanException("Expected '{$expected}', got '{$got}' at pos {$this->i}");
        }

        $this->advance();
    }

    private function advance(): string
    {
        if ($this->i < $this->len) {
            return $this->chars[$this->i++];
        }

        throw new ScanException("Unexpected end of input");
    }
}

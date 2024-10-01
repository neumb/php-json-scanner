<?php

use PHPUnit\Framework\TestCase;
use Neumb\JsonScanner\Scanner;
use Neumb\JsonScanner\Token;
use Neumb\JsonScanner\TokenType;

final class ScannerTest extends TestCase
{
    public function test_it_constructs_a_scanner_instance(): void
    {
        $scanner = Scanner::from("{}");

        $this->assertInstanceOf(Scanner::class, $scanner);
    }

    public function test_it_can_scan_braces(): void
    {
        $scanner = Scanner::from("{}");

        $iter = $scanner->scan();

        $this->consumeToken($iter, TokenType::LeftBrace, "{");
        $this->consumeToken($iter, TokenType::RightBrace, "}");

        $this->assertFalse($iter->valid());
    }

    public function test_it_can_scan_brackets(): void
    {
        $scanner = Scanner::from("[]");

        $iter = $scanner->scan();

        $this->consumeToken($iter, TokenType::LeftBracket, "[");
        $this->consumeToken($iter, TokenType::RightBracket, "]");

        $this->assertFalse($iter->valid());
    }

    public function test_it_can_scan_commas(): void
    {
        $scanner = Scanner::from(", , ,");

        $iter = $scanner->scan();

        $this->consumeToken($iter, TokenType::Comma, ",");
        $this->consumeToken($iter, TokenType::Comma, ",");
        $this->consumeToken($iter, TokenType::Comma, ",");

        $this->assertFalse($iter->valid());
    }

    public function test_it_can_scan_colons(): void
    {
        $scanner = Scanner::from(": : :");

        $iter = $scanner->scan();

        $this->consumeToken($iter, TokenType::Colon, ":");
        $this->consumeToken($iter, TokenType::Colon, ":");
        $this->consumeToken($iter, TokenType::Colon, ":");

        $this->assertFalse($iter->valid());
    }

    public function test_it_can_scan_numbers(): void
    {
        $scanner = Scanner::from("100 200 300");

        $iter = $scanner->scan();

        $this->consumeToken($iter, TokenType::Number, "100");
        $this->consumeToken($iter, TokenType::Number, "200");
        $this->consumeToken($iter, TokenType::Number, "300");

        $this->assertFalse($iter->valid());
    }

    public function test_it_can_scan_strings(): void
    {
        $scanner = Scanner::from('"umbrella" "puzzle" "lantern" "velvet"');

        $iter = $scanner->scan();

        $this->consumeToken($iter, TokenType::String, "umbrella");
        $this->consumeToken($iter, TokenType::String, "puzzle");
        $this->consumeToken($iter, TokenType::String, "lantern");
        $this->consumeToken($iter, TokenType::String, "velvet");

        $this->assertFalse($iter->valid());
    }

    public function test_it_can_scan_keywords(): void
    {
        $scanner = Scanner::from("true false null");

        $iter = $scanner->scan();

        $this->consumeToken($iter, TokenType::True, "true");
        $this->consumeToken($iter, TokenType::False, "false");
        $this->consumeToken($iter, TokenType::Null, "null");

        $this->assertFalse($iter->valid());
    }

    private function consumeToken(\Generator $iter, TokenType $t, string $lex): void
    {
        $token = $iter->current();
        $iter->next();
        $this->assertInstanceOf(Token::class, $token);
        $this->assertSame($t, $token->t);
        $this->assertSame($lex, $token->lex);
    }
}

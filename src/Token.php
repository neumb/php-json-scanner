<?php

declare(strict_types=1);

namespace Neumb\JsonScanner;

readonly class Token
{
    public function __construct(
        public TokenType $t,
        public string $lex,
        public int $pos,
    ) {
    }
}

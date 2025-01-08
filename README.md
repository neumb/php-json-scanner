## JSON Scanner
This library presents a JSON scanner for using in PHP projects, allowing to fine-tune the parsing process and achieve greater control over JSON structure interpretation and manipulation.

### Getting Started
```php
use Neumb\JsonScanner\Scanner;
use Neumb\JsonScanner\Token;
use Neumb\JsonScanner\TokenType;

$json = <<<'JSON'
{
  "name": "Alice",
  "age": 30,
  "address": {
    "street": "123 Main St",
    "city": "Wonderland",
    "zipCode": "12345"
  }
}
JSON;

$s = Scanner::from($json);
$tokens = $s->scan();

$token = $tokens->current();
assert($token instanceof Token);
assert($token->t === TokenType::LeftBrace);
assert($token->lex === "{");

$tokens->next();

$token = $tokens->current();
assert($token instanceof Token);
assert($token->t === TokenType::String);
assert($token->lex === "name");
```

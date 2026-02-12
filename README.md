## JSON Scanner
A JSON scanner that provides granular control over JSON manipulation.
This project is implemented for educational purposes.

### Example
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

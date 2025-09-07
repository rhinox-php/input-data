# InputData

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-blue.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Tests](https://img.shields.io/badge/tests-100%25%20coverage-brightgreen.svg)](phpunit.xml)

InputData is a PHP library that helps parse user input data, or data from sources that could potentially be invalid, changed, or corrupted. It parses data based on expectations with sensible defaults and robust type casting. It does not replace validation or sanitization, but provides best-effort parsing with predictable behavior.

## Features

- **Safe type casting** with fallback defaults
- **Dot notation** for nested data access
- **Inbuilt data types**: strings, integers, decimals, booleans, dates, arrays, JSON
- **Easily extendable** for custom data types
- **Mutable and Immutable** variants for different use cases
- **ArrayAccess, Countable, Iterable** interfaces for seamless integration

## Installation

```sh
composer require rhinox/input-data
```

## Quick Start

```php
use Rhino\InputData\InputData;

$data = new InputData([
    'name' => 'John Doe',
    'age' => '30',
    'active' => 'true',
    'profile' => [
        'email' => 'john@example.com'
    ]
]);

echo $data->string('name');           // "John Doe"
echo $data->int('age');              // 30
echo $data->bool('active');          // true
echo $data->string('profile.email'); // "john@example.com" (dot notation)
echo $data->string('missing', 'N/A'); // "N/A" (default value)
```

## Use Cases

### User Input Processing
```php
// HTTP request data
$post = new InputData($_POST);
$get = new InputData($_GET);

// JSON API requests
$body = InputData::tryJsonDecode(file_get_contents('php://input'));
```

### API Response Handling
```php
$response = (new \GuzzleHttp\Client())->get('https://api.example.com/users/1');
$data = InputData::tryJsonDecode($response->getBody());

$userId = $data->int('id');
$userName = $data->string('name', 'Unknown User');
$createdAt = $data->dateTime('created_at', 'UTC');
```

### File Processing
```php
// JSON files
$config = InputData::tryJsonDecodeFile('config.json');
$apiKey = $config->string('api_key');
```

```php
// CSV processing
function readCsv(string $file) {
    $handle = fopen($file, 'r');
    for ($i = 0; $row = fgetcsv($handle); ++$i) {
        yield $i => new InputData($row);
    }
    fclose($handle);
}

foreach (readCsv('data.csv') as $i => $row) {
    if ($i === 0) continue; // Skip header
    $name = $row->string(0);
    $amount = $row->decimal(1);
    $date = $row->dateTime(2, 'UTC');
}
```

## API Reference

All methods accept a key parameter and optional default value. Keys support dot notation for nested data access.

### Type Casting Methods

#### string()
```php
$data->string(?string $key = null, ?string $default = ''): ?string
```

Cast value to string. Non-string values are converted using PHP's string casting rules.

```php
$data = new InputData(['name' => 'John', 'age' => 30, 'tags' => ['dev', 'php']]);

$data->string('name');           // "John"
$data->string('age');            // "30"
$data->string('missing');        // ""
$data->string('missing', 'N/A'); // "N/A"
$data->string('tags');           // "" (arrays can't be cast to string)
```

#### int()
```php
$data->int(?string $key = null, ?int $default = 0): ?int
```

Cast value to integer. Non-numeric values return the default.

```php
$data = new InputData(['count' => '42', 'price' => '19.99', 'name' => 'John']);

$data->int('count');           // 42
$data->int('price');           // 19
$data->int('name');            // 0 (default)
$data->int('missing', 100);    // 100
```

#### decimal()
```php
$data->decimal(?string $key = null, ?float $default = 0.0): ?float
```

Cast value to float/decimal.

```php
$data = new InputData(['price' => '19.99', 'count' => '5', 'name' => 'John']);

$data->decimal('price');        // 19.99
$data->decimal('count');        // 5.0
$data->decimal('name');         // 0.0 (default)
$data->decimal('missing', 1.5); // 1.5
```

#### bool()
```php
$data->bool(?string $key = null, ?bool $default = false): ?bool
```

Cast value to boolean.

```php
$data = new InputData(['active' => 'true', 'count' => '0', 'name' => 'John']);

$data->bool('active');          // true
$data->bool('count');           // false
$data->bool('name');            // true (non-empty string)
$data->bool('missing');         // false (default)
```

#### dateTime()
```php
$data->dateTime(?string $key, ?string $timezone = null, ?string $default = null): ?\DateTimeImmutable
```

Parse value as DateTime with optional timezone.

```php
$data = new InputData(['created' => '2023-07-15 10:30:00', 'timestamp' => '@1689422400']);

$data->dateTime('created');                    // DateTimeImmutable (server timezone)
$data->dateTime('created', 'UTC');             // DateTimeImmutable (UTC)
$data->dateTime('timestamp');                  // DateTimeImmutable from timestamp
$data->dateTime('missing', null, 'now');       // Current time
$data->dateTime('invalid', null, null);        // null
```

### Data Structure Methods

#### arr()
```php
$data->arr(?string $key = null, ?array $default = []): InputData
```

Get array data as new InputData instance.

```php
$data = new InputData(['users' => [['name' => 'John'], ['name' => 'Jane']]]);

$users = $data->arr('users');
foreach ($users as $user) {
    echo $user->string('name'); // Access nested data
}

$data->arr('users')->count();     // 2
$data->arr('missing')->isEmpty(); // true
```

#### object()
```php
$data->object(?string $key = null, $default = null): InputData
```

Get object data, converting arrays to objects when needed.

```php
$data = new InputData(['config' => ['debug' => true, 'timeout' => 30]]);

$config = $data->object('config');
$debug = $config->bool('debug'); // true
```

#### json()
```php
$data->json(?string $key = null, $default = []): InputData
```

Parse JSON string into InputData instance.

```php
$data = new InputData(['response' => '{"status":"ok","data":[1,2,3]}']);

$response = $data->json('response');
$status = $response->string('status');      // "ok"
$items = $response->arr('data')->count();   // 3
```

#### raw()
```php
$data->raw(string $key, $default = null): mixed
```

Get raw value without type casting.

```php
$data = new InputData(['items' => ['a', 'b', 'c'], 'count' => '5']);

$data->raw('items');  // ['a', 'b', 'c'] (original array)
$data->raw('count');  // '5' (original string)
```

### Utility Methods

#### exists()
```php
$data->exists(string $key): bool
```

Check if a key exists (supports dot notation).

```php
$data = new InputData(['user' => ['profile' => ['name' => 'John']]]);

$data->exists('user');              // true
$data->exists('user.profile.name'); // true
$data->exists('user.missing');      // false
```

#### isEmpty(), isArray(), count()
```php
$data->isEmpty(): bool
$data->isArray(): bool
$data->count(): int
```

Check data state and get count.

```php
$data = new InputData(['a', 'b', 'c']);
$data->isEmpty(); // false
$data->isArray(); // true
$data->count();   // 3
```

## Static Factory Methods

### JSON Processing

#### jsonDecode()
```php
InputData::jsonDecode(string $jsonString, bool $assoc = true): InputData
```

Parse JSON string, throws `JsonException` on invalid JSON.

```php
try {
    $data = InputData::jsonDecode('{"name":"John","age":30}');
    echo $data->string('name'); // "John"
} catch (\JsonException $e) {
    // Handle parsing error
}
```

#### tryJsonDecode()
```php
InputData::tryJsonDecode(string $jsonString, bool $assoc = true): InputData
```

Parse JSON string, returns empty InputData on invalid JSON.

```php
$data = InputData::tryJsonDecode('invalid json');
$data->isEmpty(); // true - no exception thrown
```

#### jsonDecodeFile()
```php
InputData::jsonDecodeFile(string $filename, bool $assoc = true): InputData
```

Read and parse JSON file, throws `FileReadException` or `JsonException` on errors.

```php
$config = InputData::tryJsonDecodeFile('config.json');
$apiKey = $config->string('api_key');
```

#### tryJsonDecodeFile()
```php
InputData::tryJsonDecodeFile(string $filename, bool $assoc = true): InputData
```

Read and parse JSON file, returns empty InputData on any error.

```php
$config = InputData::tryJsonDecodeFile('config.json');
if (!$config->isEmpty()) {
    $apiKey = $config->string('api_key');
}
```

## Data Modification

Two variants are available for modifying data:

- **MutableInputData**: Methods modify the current instance
- **ImmutableInputData**: Methods return new instances

### MutableInputData

```php
use Rhino\InputData\MutableInputData;

$data = new MutableInputData(['a' => 1, 'b' => 2]);

$data->set('c', 3);              // Modifies $data
$data->extend(['d' => 4]);       // Modifies $data
$data->filter(fn($v) => $v->int() > 2); // Modifies $data

echo $data->count(); // 2 (only c=3, d=4 remain)
```

### ImmutableInputData

```php
use Rhino\InputData\ImmutableInputData;

$original = new ImmutableInputData(['a' => 1, 'b' => 2]);

$modified = $original->set('c', 3);              // Returns new instance
$extended = $original->extend(['d' => 4]);       // Returns new instance
$filtered = $original->filter(fn($v) => $v->int() > 1); // Returns new instance

echo $original->count(); // 2 (unchanged)
echo $modified->count(); // 3 (new instance)
```

### Modification Methods

#### extend()
```php
$data->extend(array ...$newData): static
```

Recursively merge arrays into existing data.

```php
$data = new MutableInputData(['user' => ['name' => 'John']]);
$data->extend(['user' => ['email' => 'john@example.com'], 'active' => true]);
// Result: ['user' => ['name' => 'John', 'email' => 'john@example.com'], 'active' => true]
```

#### set()
```php
$data->set(string $name, $value): static
```

Set value at path (supports dot notation).

```php
$data = new MutableInputData([]);
$data->set('user.profile.name', 'John');
$data->set('user.profile.age', 30);
// Result: ['user' => ['profile' => ['name' => 'John', 'age' => 30]]]
```

#### unset()
```php
$data->unset(string $name): static
```

Remove value at path (supports dot notation).

```php
$data = new MutableInputData(['user' => ['name' => 'John', 'email' => 'john@example.com']]);
$data->unset('user.email');
// Result: ['user' => ['name' => 'John']]
```

#### filter()
```php
$data->filter(?callable $callback = null): static
```

Filter data using callback. Default callback removes null values.

```php
$data = new MutableInputData(['a' => 1, 'b' => null, 'c' => 3]);
$data->filter(); // Removes null values
// Result: ['a' => 1, 'c' => 3]

$data->filter(fn($value) => $value->int() > 1);
// Result: ['c' => 3]
```

#### filterRecursive()
```php
$data->filterRecursive(?callable $callback = null): static
```

Recursively filter nested data.

```php
$data = new MutableInputData([
    'users' => [
        ['name' => 'John', 'active' => true],
        ['name' => 'Jane', 'active' => false]
    ]
]);
$data->filterRecursive(fn($value, $key) => $key->string() !== 'active' || $value->bool());
```

#### map()
```php
$data->map(callable $callback): static
```

Transform values using callback.

```php
$data = new MutableInputData(['a' => 1, 'b' => 2, 'c' => 3]);
$data->map(fn($value) => $value->int() * 2);
// Result: ['a' => 2, 'b' => 4, 'c' => 6]
```

#### mapRecursive()
```php
$data->mapRecursive(callable $callback): static
```

Recursively transform nested data.

```php
$data = new MutableInputData(['numbers' => [1, 2], 'single' => 3]);
$data->mapRecursive(fn($value) => $value->int() * 2);
// Result: ['numbers' => [2, 4], 'single' => 6]
```

#### values()
```php
$data->values(): static
```

Re-index array to sequential numeric keys.

```php
$data = new MutableInputData(['a' => 1, 'c' => 2, 'x' => 3]);
$data->values();
// Result: [0 => 1, 1 => 2, 2 => 3]
```

#### merge()
```php
$data->merge($data): static
```

Merge with another array or InputData instance.

```php
$data1 = new MutableInputData(['a' => 1, 'b' => 2]);
$data1->merge(['c' => 3, 'd' => 4]);
// Result: ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]
```

## Advanced Features

### Dot Notation
Access nested data using dot-separated keys:

```php
$data = new InputData([
    'user' => [
        'profile' => [
            'social' => [
                'twitter' => '@johndoe'
            ]
        ]
    ]
]);

$twitter = $data->string('user.profile.social.twitter'); // "@johndoe"
```

### Array Access Interface
InputData implements ArrayAccess for convenient access:

```php
$data = new InputData(['name' => 'John', 'age' => 30]);

echo $data['name']->string(); // "John"
echo $data['age']->int();     // 30
isset($data['name']);         // true

// Note: Setting/unsetting throws MutationException for base InputData
```

### Iteration
InputData implements IteratorAggregate:

```php
$data = new InputData(['a' => 1, 'b' => 2, 'c' => 3]);

foreach ($data as $key => $value) {
    echo $key->string() . ': ' . $value->int();
}
```

### Find Method
Search for items matching criteria:

```php
$users = new InputData([
    ['name' => 'John', 'active' => true],
    ['name' => 'Jane', 'active' => false],
    ['name' => 'Bob', 'active' => true]
]);

$activeUser = $users->find(fn($user) => $user->bool('active'));
echo $activeUser->string('name'); // "John"
```

## Extending InputData

Create custom parsers by extending the base class:

```php
class CustomInputData extends InputData
{
    public function uuid(?string $name = null, ?string $default = null): ?string
    {
        $value = $this->string($name, $default);

        if ($value && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value)) {
            return $value;
        }

        return $default;
    }

    public function email(?string $name = null, ?string $default = null): ?string
    {
        $value = $this->string($name, $default);

        if ($value && filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return $value;
        }

        return $default;
    }
}

$data = new CustomInputData(['id' => 'f47ac10b-58cc-4372-a567-0e02b2c3d479']);
$uuid = $data->uuid('id'); // Returns the UUID or null if invalid
```

## Development

### Requirements
- PHP 8.4 or higher
- Composer for dependency management

### Development Tools
The project uses modern PHP development tools:

- **PHPUnit**: Testing framework with 100% code coverage
- **PHP CS Fixer**: Code formatting and style checking
- **Psalm**: Static analysis for type safety
- **Infection**: Mutation testing

### Running Tests
```bash
# Run all tests
composer test:unit

# Run tests with coverage report
composer test:coverage

# Run tests with text coverage
composer test:coverage-text
```

### Code Quality
```bash
# Check code style
composer lint:check

# Fix code style issues
composer lint:fix

# Run static analysis
composer analyze

# Run full quality assurance suite
composer qa
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes with tests
4. Run the quality assurance suite (`composer qa`)
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

Please ensure all tests pass and maintain 100% code coverage.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

# InputData

InputData is a PHP library that helps parse user inputted data, or data that comes from sources that could potentially be invalid, changed, or corrupted etc. It parses data based on expectations and sensible defaults. It does not replace validation or sanitization, but rather used for best effort based parsing.

## Installation

```sh
composer require rhinox/input-data
```

## Basic usage

```php
use Rhino\InputData\InputData;

$data = new InputData([
    'foo' => 'bar',
]);

// or

try {
    $data = InputData::jsonDecode('{"foo":"bar"}');
} catch (\Rhino\InputData\ParseException $exception) {
    // Handle invalid JSON
}

echo $data->string('foo'); // outputs "bar"
```

### Use cases

 - Handling user input (PHP, Laravel)
 - Handling API data
 - Reading/importing files (CSV, JSON)

#### User input

```php
$post = new InputData($_POST);
$get = new InputData($_GET);
$body = InputData::jsonDecode(file_get_contents('php://input'));
```

#### API Data

```php
$response = (new \GuzzleHttp\Client())->get('https://api.agify.io/?name=petah');
$data = InputData::jsonDecode($response->getBody());
```

#### Reading files

```php
function readCsv(string $file) {
    $handle = fopen($file, 'r');
    for ($i = 0; $row = fgetcsv($handle); ++$i) {
        yield $i => new InputData($row);
    }
    fclose($handle);
}

foreach (readCsv('csv-data.csv') as $i => $row) {
    if ($i->int() === 0) continue;
    $name = $row->string(0);
    $number = $row->decimal(1);
    $date = $row->dateTime(2, 'UTC');
}
```

### Methods

Example data used below:

```php
$data = new InputData([
    'foo' => 'bar',
    'seven' => '7.5',
    'july3' => '2021-07-03 11:45:30',
    'fruit' => ['apple', 'banana', 'pear'],
]);
```

#### String

```php
$data->string(string $key, ?string $default): ?string
```

```php
$data->string('foo') // "bar"
$data->string('seven') // "7.5"
$data->string('baz') // ""
$data->string('baz', null) // null
$data->string('baz', 'qux') // "qux"
$data->string('fruit') // ""
```

#### Integer

```php
$data->int(int $key, ?int $default): ?int
```

```php
$data->int('foo') // int(0)
$data->int('seven') // int(7)
$data->int('baz') // int(0)
$data->int('baz', 12) // int(12)
$data->int('baz', null) // null
$data->int('fruit') // int(0)
```

#### Decimal

```php
$data->decimal(string $key, ?float $default): ?float
```

```php
$data->decimal('foo') // float(0)
$data->decimal('seven') // float(7.5)
$data->decimal('baz') // float(0)
$data->decimal('baz', 12.5) // float(12.5)
$data->decimal('baz', null) // null
$data->decimal('fruit') // float(0)
```

#### DateTime

```php
$data->dateTime(?string $name, ?string $timezone = null, ?string $default = null): ?DateTimeImmutable
```

```php
$data->dateTime('july3', 'Pacific/Auckland') // object(DateTimeImmutable) Pacific/Auckland
$data->dateTime('july3') // object(DateTimeImmutable) server timezone
$data->dateTime('baz') // null
$data->dateTime('baz', null, 'now') // object(DateTimeImmutable) current date/time
```

#### Array

```php
$data->arr(string $key, ?array $default): InputData
```

```php
// Output each item of an array
foreach ($data->arr('fruit') as $fruit) {
    echo $fruit->string();
}
$data->arr('fruit')->getData() // ["apple", "banana", "pear"]
$data->arr('fruit')->count() // int(3)
$data->arr('fruit')->isEmpty() // false
$data->arr('fruit')->isArray() // true
$data->string('fruit.0') // "apple"

// Outputs nothing, treated as empty array
foreach ($data->arr('baz') as $baz) {
    echo $baz->string();
}
$data->arr('baz')->getData() // array(0)
$data->arr('baz')->count() // int(0)
$data->arr('baz')->isEmpty() // true
$data->arr('baz')->isArray() // false
$data->string('baz.0') // ""
```

### Other methods

```php
$data->json(string $key, ?mixed $default): InputData
```

Parse a JSON string and returns an InputData instance.

```php
$data->raw(string $key, ?mixed $default): mixed
```

Returns the raw data item in an array.

```php
$data->getData(): mixed
```

Returns the raw data as is.

### Modifying Data

Two classes are avalible that allow the data to be modified:

 - MutableInputData _all methods modify the data in the current instance_
 - ImmutableInputData _returns a new instance with the modified data_

```php
$data->extend(array ...$newData): static
```

```php
$data->filter(?callable $callback = null): static
```

```php
$data->mapRecursive(callable $callback): static
```

```php
$data->values(): static
```

```php
$data->set(string $name, $value): static
```

```php
$data->unset(string $name): static
```

### Extending InputData

You can extend the base InputData class and implement your own type parsing. Some examples include:

 - UUID _examples/uuid.php_
 - JWT _examples/jwt.php_
